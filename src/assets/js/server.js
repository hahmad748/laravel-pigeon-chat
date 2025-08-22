import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';
import Redis from 'ioredis';

const app = express();
const http = createServer(app);

const io = new Server(http, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        allowedHeaders: ["my-custom-header"],
        credentials: true
    },
    transports: ['polling', 'websocket']
});

const redis = new Redis();
let users = [];
let userChannels = new Map(); // Track which channels each user is subscribed to

http.listen(8005, () => {
    console.log('listening on *:8005');
});

// Subscribe to all possible channels
const channels = [
    'private-channel',           // Legacy channel for backward compatibility
    'user-chat',                // Individual user-to-user messages
    'group-chat',               // Group chat messages
    'user-status',              // User online/offline status
    'typing-indicators',        // Typing indicators
    'message-seen'              // Message seen confirmations
];

channels.forEach(channel => {
    redis.subscribe(channel, () => {
        console.log(`Subscribed to ${channel}`);
    });
});

redis.on('message', (channel, message) => {
    console.log(`Redis message received on channel ${channel}:`, message);
    
    try {
        const data = JSON.parse(message);
        console.log('Parsed Redis data:', data);
        
        switch (channel) {
            case 'private-channel':
                // Legacy channel - broadcast to all (for backward compatibility)
                io.emit(channel + ':' + data.event, data.data.data);
                break;
                
            case 'user-chat':
                console.log('Processing user-chat message:', data);
                // Individual chat - only send to specific users
                handleUserChat(data);
                break;
                
            case 'group-chat':
                console.log('Processing group-chat message:', data);
                // Group chat - send to all group members
                handleGroupChat(data);
                break;
                
            case 'user-status':
                // User status updates
                io.emit('user-status-update', data.data.data);
                break;
                
            case 'typing-indicators':
                // Typing indicators
                handleTypingIndicator(data);
                break;
                
            case 'message-seen':
                // Message seen confirmations
                console.log('🔍 Redis message-seen channel received:', data);
                handleMessageSeen(data);
                break;
                
            default:
                console.log(`Unknown channel: ${channel}`);
        }
    } catch (error) {
        console.error('Error processing message:', error);
        console.error('Raw message:', message);
    }
});

// Handle individual user chat messages
function handleUserChat(message) {
    const { from_id, to_id } = message.data;
    
    console.log('🔍 handleUserChat called with:', { from_id, to_id, message: message.data });
    
    // Send to both users using the event names the frontend expects
    io.to(`user_${from_id}`).emit('user-chat', message.data);
    io.to(`user_${to_id}`).emit('user-chat', message.data);
}

// Handle group chat messages
function handleGroupChat(message) {
    const { to_id } = message.data; // to_id is the group_id for group messages
    
    console.log('🔍 handleGroupChat called with:', { to_id, message: message.data });
    
    // Send to all users (client will filter by group)
    io.emit('group-chat', message.data);
}

// Handle typing indicators
function handleTypingIndicator(message) {
    const { from_id, to_id, type, typing } = message.data;
    
    if (type === 'user') {
        // Individual chat typing
        io.to(`user_${to_id}`).emit('typing-indicator', {
            from_id,
            typing
        });
    } else if (type === 'group') {
        // Group chat typing
        io.emit('group-typing-indicator', {
            group_id: to_id,
            from_id,
            typing
        });
    }
}

// Handle message seen confirmations
function handleMessageSeen(message) {
    const { from_id, to_id, type, seen } = message;
    
    console.log('🔍 handleMessageSeen called with:', { from_id, to_id, type, seen });
    
    if (type === 'user') {
        // Individual chat seen - notify the original sender
        console.log(`🔍 Emitting message-seen to user_${from_id}:`, {
            from_id: from_id,  // The original sender
            to_id: to_id,      // The person who marked it as seen
            seen
        });
        io.to(`user_${from_id}`).emit('message-seen', {
            from_id: from_id,  // The original sender
            to_id: to_id,      // The person who marked it as seen
            seen
        });
    } else if (type === 'group') {
        // Group chat seen - notify all group members
        console.log(`🔍 Emitting group-message-seen to all:`, {
            group_id: to_id,
            from_id: from_id,  // The person who marked it as seen
            seen
        });
        io.emit('group-message-seen', {
            group_id: to_id,
            from_id: from_id,  // The person who marked it as seen
            seen
        });
    }
}

io.on('connection', (socket) => {
    let userId = null;
    let userName = null;
    let userRooms = new Set(); // Track which rooms/channels user is in

    socket.on('user-connected', (user_id, user_name) => {
        userId = user_id;
        userName = user_name;
        
        // Join user's personal room
        socket.join(`user_${user_id}`);
        userRooms.add(`user_${user_id}`);
        
        // Track connected user
        users.push({ id: user_id, name: user_name, socketId: socket.id });
        userChannels.set(user_id, userRooms);
        
        // Notify others that user is online
        socket.broadcast.emit('userOnline', user_id, user_name);
        console.log(`User connected: ${user_name} (ID: ${user_id})`);
    });

    socket.on('join-group', (group_id) => {
        if (userId) {
            const roomName = `group_${group_id}`;
            socket.join(roomName);
            userRooms.add(roomName);
            userChannels.set(userId, userRooms);
            console.log(`User ${userName} joined group ${group_id}`);
        }
    });

    socket.on('leave-group', (group_id) => {
        if (userId) {
            const roomName = `group_${group_id}`;
            socket.leave(roomName);
            userRooms.delete(roomName);
            userChannels.set(userId, userRooms);
            console.log(`User ${userName} left group ${group_id}`);
        }
    });

    socket.on('typing', (data) => {
        console.log(`Typing event received:`, data);
        console.log(`Typing: ${data.from_id} -> ${data.to_id} (typing: ${data.typing}, type: ${data.type})`);
        
        if (data.type === 'group') {
            // Group chat typing
            socket.to(`group_${data.to_id}`).emit('group-typing', data);
        } else {
            // Individual chat typing
            socket.to(`user_${data.to_id}`).emit('client-typing', data);
        }
    });

    socket.on('sendMessage', (data) => {
        console.log(`Contact update event received:`, data);
        console.log(`Contact update: ${data.sender_id} -> ${data.receiver_id} (type: ${data.type}, updating: ${data.updating})`);
        
        // This event is for contact item updates, not actual message content
        // Actual messages are handled by Laravel broadcasting via PrivateMessageEvent
        
        if (data.type === 'group') {
            // Group contact update
            io.to(`group_${data.receiver_id}`).emit('group-contact-update', data);
        } else {
            // Individual contact update
            io.to(`user_${data.receiver_id}`).emit('client-contactItem', data);
            io.to(`user_${data.sender_id}`).emit('client-contactItem', data);
            io.emit('update-conversation', data);
        }
    });

    socket.on('seen', (data) => {
        if (data.type === 'user') {
            // Individual message seen
            socket.to(`user_${data.from_id}`).emit('client-seen', data);
        } else if (data.type === 'group') {
            // Group message seen
            socket.to(`group_${data.to_id}`).emit('group-seen', data);
        }
    });

    socket.on('disconnect', () => {
        if (userId) {
            // Remove user from tracking
            users = users.filter(user => user.id !== userId);
            userChannels.delete(userId);
            
            // Notify others that user is offline
            socket.broadcast.emit('user-disconnect', userId, userName);
            console.log(`User disconnected: ${userName} (ID: ${userId})`);
        }
    });
});

// Utility function to get users in a specific group
function getUsersInGroup(groupId) {
    // This would ideally query the database or maintain a group membership map
    // For now, return all connected users (client will filter)
    return users.map(user => user.id);
}

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        connected_users: users.length,
        channels: channels,
        timestamp: new Date().toISOString()
    });
});

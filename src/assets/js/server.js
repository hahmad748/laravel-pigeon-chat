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

http.listen(8005, () => {
    console.log('listening on *:8005');
});

redis.subscribe('private-channel', () => {
    console.log('Subscribed to private channel');
});

redis.on('message', (channel, message) => {
    message = JSON.parse(message);
    if (channel === 'private-channel') {
        console.log(channel + ":" + message.event);
        io.emit(channel + ':' + message.event, message.data.data);
    }
});

io.on('connection', (socket) => {
    let id = null;
    let name = null;

    socket.on('user-connected', (user_id, user_name) => {
        id = user_id;
        name = user_name;
        io.emit('userOnline', user_id, user_name);
        console.log("user-connected-" + user_name);
    });

    socket.on('disconnect', () => {
        console.log('User Disconnect-' + name);
        io.emit('user-disconnect', id, name);
    });

    socket.on('typing', (data) => {
        console.log('typing-' + data.from_id);
        io.emit('client-typing', data);
    });

    socket.on('sendMessage', (data) => {
        console.log('ContactListUpdate-' + data.sender_id);
        console.log('sendMessage', data.receiver_id);
        io.emit('client-contactItem', data);
        console.log("Sending Conversation update " + JSON.stringify(data));
        io.emit('update-conversation', data);
    });

    socket.on('seen', (data) => {
        io.emit('client-seen', data);
    });
});

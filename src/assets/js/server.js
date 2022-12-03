const app = require('express')();
const http = require('http').Server(app);

var io = require('socket.io')(http, {
        cors: {
            origin: "*",
            methods: ["GET", "POST"],
            allowedHeaders: ["my-custom-header"],
            credentials: true
        },
        transports :['polling', 'websocket']
    }
    );
var Redis = require('ioredis');
var redis = new Redis();
var users = [];
http.listen(8005, () => {
    console.log('listening on *:8005');
});

redis.subscribe('private-channel',function () {
    console.log('Subscribe to private channel');
});
redis.on('message',function (channel,message) {

    message = JSON.parse(message)
    if (channel == 'private-channel')
    {

        console.log(channel+":"+message.event);
        io.emit(channel+':'+message.event,message.data.data)
    }

})

io.on('connection',function (socket) {
    var id = null;
    var name=null;
    socket.on('user-connected',function (user_id,user_name) {
        id=user_id;
        name=user_name
        user_id = user_id;
        user_name = user_name
        io.emit('userOnline',user_id,user_name)
        console.log("user-connected-" + user_name);
    });

    socket.on('disconnect',function () {
        // console.log('ds');
        console.log('User Disconnect-'+name);
        io.emit('user-disconnect',id,name)
    })
    socket.on('typing',function (data) {

        console.log('typing-'+data.from_id)
        io.emit('client-typing',data)

    })

    socket.on('sendMessage',function (data) {

        console.log('ContactListUpdate-'+data.sender_id)
        console.log('sendMessage',data.receiver_id)
        io.emit('client-contactItem',data)
        console.log("Sending Conversation update"+JSON.stringify(data))
        io.emit('update-conversation',data)


    })
    socket.on('seen',function (data) {
        io.emit('client-seen',data);
    })

    // socket.on('user-disconnect',function (user_id,user_name) {
    //     user_id = user_id;
    //     user_name = user_name
    //     io.emit('userDisconnect',user_id,user_name)
    //     console.log("user-disconnect-" + user_name);
    //
    // })
});

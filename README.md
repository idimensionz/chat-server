# iDimensionz Websocket Chat Server

Github: https://github.com/idimensionz/chat-server
## Requirements
* PHP 7.1 or higher
* [Composer]

## Installation Instructions
1. Clone the repository
2. Run `composer install` to install the dependencies

## Starting the chat server
Run `php bin/chat-server.php`

## Chat clients
### From the web broweser JS console
You can test the chat server right from a web browswer JS console by running the code below.

Note: You'll want to run this from at least 2 browser tabs/windows to be able to see messages flow between the 2 "clients".  
> var conn = new WebSocket('ws://localhost:8080');
conn.onopen = function(e) {
 console.log("Connection established!");
};
> 
> conn.onmessage = function(e) {
 console.log(e.data);
};
> 
> conn.send('Test message');

### Use our ReactJS Chat component
**Coming soon!**

[Composer]: http://getcomposer.org
var expect = chai.expect;

describe('websocket', function() {
    "use strict";

    describe('websocket to WsServer', function() {
        "use strict";
        /**
         *  Tests requires websocket router working
         */
        var url = "192.168.178.157";
        var port = "8051";
        var realm = "nubelum.lockate";


        it('must create a connection object (websocket)', function() {
            var connection = createConnection(url, port, realm);
            //console.log(connection);
            expect(connection).to.be.an('object');
        });

        it('must open a connection', function(done) {
            var connection = createConnection(url, port, realm);
            connection.aDivID = "websocket-connection-tester";
            openConnection(connection);
            var status = document.getElementById("websocket-connection-tester");
            setTimeout(function() {
                expect(status.innerText).to.be.equal("Connected");
                done();
            }, 500);
        });

        it('must publish/subscribe on a topic', function(done) {
            "use strict";
            var connection = createConnection(url, port, realm);
            var subscriber = {
                topic: 'lockate.gateways',
                handler: function(args) {
                    //console.log('args[0]: ' + args[0]);
                    document.getElementById("pubsub-tester").innerText = args[0];
                }
            };
            // Important: By default, a publisher will not receive an event it publishes
            //      even when the publisher is itself subscribed to the topic subscribed to.
            //      This behavior can be overridden by passing
            //          exclude_me: False (as below)
            //      in the options.
            var publisher = {
                topic: 'lockate.gateways',
                args: ['The new order Maya-Gandul'],
                kwargs: {},
                options: {exclude_me: false}
            };
            openConnection(connection, subscriber, publisher);
            var published = document.getElementById("pubsub-tester");
            setTimeout(function() {
                expect(published.innerText).to.be.equal(String(publisher.args[0]));
                done();
            }, 500);
        });

    });

});

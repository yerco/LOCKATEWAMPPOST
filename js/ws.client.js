/**
 * ws.client.js
 * Implementation for nubelum pubsub on browser using autobahn
 * https://github.com/crossbario/autobahn-js
 *
 * @license Nubelum
 * @version 0.1
 * @author  Calamandes yerco@hotmail.com
 * @updated 2018-06-13
 * @link    http://www.nubelum.com
 */

/**
 * Creates a connection to a router implementing WAMPv2
 * @param {string} url IP address
 * @param {string} port port's number
 * @param realm - string
 * @returns {connection.Connection|Connection}
 */
function createConnection(url, port, realm) {
    "use strict";
    return new autobahn.Connection({
        url: "ws://" + url + ":" + port,
        realm: realm
    });
}

/**
 * Opens a Connection
 * subscriber and publisher not assigned null as default -
 *      such feature is only available on ES6/ES2015.
 *
 * @param {connection.Connection|Connection} connection
 * @param {object} subscriber - object with properties:
 *      - topic
 *      - handler - to be used by publisher
 * @param {object} publisher - object with properties:
 *      - topic
 *      - args - passed to subscriber's handler
 *      - kwargs
 *      - options
 */
function openConnection(connection, subscriber, publisher) {
    "use strict";
    connection.onopen = function (session, details) {
        // Publish, Subscribe, Call and Register
        if (connection.aDivID) {
            document.getElementById(connection.aDivID).innerText = "Connected";
            document.getElementById(connection.aDivID).style.color = "green";
        }
        if (subscriber) {
            subscribeToTopic(session, subscriber);
        }
        if (publisher) {
            publishOnTopic(session, publisher);
        }

    };

    connection.onclose = function (reason, details) {
        // handle connection lost
        document.getElementById("websocket-connection-tester").innerText = "Not Connected";
        document.getElementById("websocket-connection-tester").style.color = "red";
    };

    connection.open();
}

/**
 *
 * @param {Session} session
 * @param {object} subscriber - object with properties:
 *      - topic
 *      - handler - to be used by publisher
 */
function subscribeToTopic(session, subscriber) {
    "use strict";
    session.subscribe(subscriber.topic, subscriber.handler);
}

/**
 *
 * @param session
 * @param {object} publisher - object with properties:
 *      - topic
 *      - args - passed to subscriber's handler
 *      - kwargs
 *      - options
 */
function publishOnTopic(session, publisher) {
    "use strict";
    console.log(publisher);
    session.publish(
        publisher.topic,
        publisher.args,
        publisher.kwargs,
        publisher.options
    );
}


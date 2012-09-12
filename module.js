
// Create a new module, which will store the relevant bitcode scripts.
M.mod_bitcode = {};

// Specify a default URL to use transmission and receipt
M.mod_bitcode.send_receive_uri =  M.cfg.wwwroot + '/mod/bitcode/ajax/sendreceive.php';

//Refresh rate, in ms.
M.mod_bitcode.refresh_rate = 1000;

/**
 *  This call-back displays the results of a query to the syntax checker.
 *  resp: The response object from the syntax checker; which is automatically used to populate the "response text" div.
 */
M.mod_bitcode.receive = function(id, resp) {

    // Set the value of the "dynamic errors" div to match the response from the syntax checker.
    // TODO: Adjust this to allow multiple dynamic editors per page?
	YUI().use("node", function(Y) { Y.one("#from").setContent(resp.responseText); } );
};

/**
 *  Runs a single iteration of the transmit/receive function.
 */
M.mod_bitcode.send_receive = function () {

    YUI().use("node", "io", function(Y) {

        // Encode the user-script in a format that can be sent to our safe evaluator.
        var message = encodeURIComponent(Y.one("#to").get('value'));
        var toChannel = parseInt(Y.one("#tochannel").get('value'));
        var fromChannel = parseInt(Y.one("#fromchannel").get('value'));

        // Create a new callback list which will describe how to handle AJAX responses.
        var check_callback = {};

        // Start a new POST-request string.
        var postdata = 'id=' + M.mod_bitcode.cmid + "&tx=" + message;


        // If we have a valid tx channel, then transmit the message.
        if(!isNaN(toChannel) && toChannel >= 0) {
           postdata += "&txchan=" + toChannel;
        }

        // If we have a valid rx channel, try to receive a message:
        if(!isNaN(fromChannel) && fromChannel >= 0) {

            // Set the receive channel.
            postdata += "&rxchan=" + fromChannel;

            // Create a "callback" object which will automatically display the results after the AJAX query is complete.
            Y.on('io:complete', M.mod_bitcode.receive, Y);
        }

        var async = {
            method: 'GET',
            data: postdata
        }

        // Request that our code is sent/received, as appropriate.
        //YAHOO.util.Connect.asyncRequest('POST', M.mod_bitcode.send_receive_uri, check_callback);
        Y.io(M.mod_bitcode.send_receive_uri, async);
    });
};


M.mod_bitcode.enforce_binary = function (e) { 
    var key = e.keyCode;

    console.log(e);

    //don't allow non-binary keys
    if(     key != 8 /* backspace */ &&
			key != 9 /* tab */ &&
			key != 13 /* enter */ &&
			key != 35 /* end */ &&
			key != 36 /* home */ &&
			key != 37 /* left */ &&
			key != 39 /* right */ &&
			key != 46 /* del */ &&
            key != 48  && // 0&&
            key != 49 && // up
            key != 17 && //ctrl
            key != 18 && // alt
            key != 96 &&
            key != 97
        ) 
    {
        e.halt();
    }

    //strip all non-zeroes
    M.mod_bitcode.strip_nonbinary.call(this);
};

M.mod_bitcode.strip_nonbinary = function () {
    this.set('value', this.get('value').replace(/[^01]/g, '')); 
}

M.mod_bitcode.init = function(Y, cmid) {
    M.mod_bitcode.cmid = cmid;
    setInterval('M.mod_bitcode.send_receive()', M.mod_bitcode.refresh_rate);
    Y.on('keydown', M.mod_bitcode.enforce_binary, '#to');
    Y.on('change', M.mod_bitcode.strip_nonbinary, '#to');
};


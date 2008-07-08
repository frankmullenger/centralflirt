$(document).ready(function(){
        // count character on keyup
        function counter(){
            var maxLength     = 140;
            var currentLength = $("#status_textarea").val()
                                                     .replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, "drry")
                                                     .replace(/[\u0800-\uFFFF]/g, "drr")
                                                     .replace(/[\u0080-\u07FF]/g, "dr")
                                                     .length;
            var remaining = maxLength - currentLength;
            var counter   = $("#counter");
            counter.text(remaining);

            if (remaining <= 0) {
                counter.attr("class", "toomuch");
            } else {
                counter.attr("class", "");
            }
        }
     
        if ($("#status_textarea").length) {
            $("#status_textarea").bind("keyup", counter);
            // run once in case there's something in there
            counter();
        }
});

function doreply(nick) {
     rgx_username = /^[0-9a-zA-Z\-_.]*$/;
     if (nick.match(rgx_username)) {
          replyto = "@" + nick + " ";
          if ($("#status_textarea")) {
               $("#status_textarea").val(replyto);
               $("#status_textarea").focus();
          }
     }
     return false;
}


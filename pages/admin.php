<?php
    include "parts/nav.php";
    $key = "";
    if(isset($_COOKIE["authtk"])){
        $key = $_COOKIE["authtk"];
    }
    // The power of virtual REST request! Yeah!
    // Check to see if key is actually valid
    $api_result = $app->virtualSimpleGet("auth_tokens/$key");
    if($api_result[0] == 200){
        $loggedin = true;
    } else {
        $loggedin = false;
    }
?>


<div class='container'>
    <?php if(!$loggedin){ ?>
        <div class='not-authenticated'>
            <h1><span class='glyphicon glyphicon-lock'></span><br>Not Authenticated :(</h1>
            <h3>You should already have an authentication key.<br>Just enter it below, and you will be in the system.</h3>
            <br><br>
            <div class="input-group">
                <span class="input-group-addon">Auth Key</span>
                <input type="text" class="form-control" id="auth-key-textbox" aria-label="Authentication Key">
            </div><br><br>
            <button class='btn btn-primary' id="authenticate-button">Authenticate</button>
        </div>
        <script>
            $(function(){
                $("#authenticate-button").click(function(){
                    $.ajax({
                        url:"api/auth_tokens/" + $("#auth-key-textbox").val(),
                        type:"GET",
                        success:function(data){
                            setCookie("authtk", $("#auth-key-textbox").val(), 1);
                            window.location.reload();
                        },
                        error:function(data){
                            alert("Oops, the authentication key is not valid ...");
                        }
                    });
                });
            });
        </script>
    <?php } else { ?>
        <div class="panel panel-danger">
            <div class="panel-body">
                <button class='btn btn-danger'><span class="glyphicon glyphicon-log-out"></span> Deauthorize (Logout)</button> &nbsp;
                <button class="btn btn-danger"><span class="glyphicon glyphicon-console"></span> Show my API key</button> &nbsp;
                You are authenticated by key <code><?=substr($key, 0, 8) . str_repeat("*", 56)?></code>
            </div>
        </div>
        <div class="alert alert-danger" role="alert"><button class='btn btn-success'><span class="glyphicon glyphicon-ok"></span> Enable</button> &nbsp; <b>Submission is currently disabled.</b> Students can no longer submit or modify their choices.</div>
        <div class="alert alert-success" role="alert"><button class='btn btn-danger'><span class="glyphicon glyphicon-remove"></span> Disable</button> &nbsp; <b>Submission is currently enabled.</b> Students can now submit their choices.</div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title large"><span class="glyphicon glyphicon-tasks"></span> Statistics</h3>
            </div>
            <div class="panel-body">
                <button class='btn btn-default'><span class="glyphicon glyphicon-save"></span> Download CSV</button>
                <button class='btn btn-default'><span class="glyphicon glyphicon-save"></span> Download JSON</button>
                <button class='btn btn-default'><span class="glyphicon glyphicon-save"></span> Download XML</button>
                <button class='btn btn-default'><span class="glyphicon glyphicon-book"></span> API Doc</button>
            </div>
        </div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title large"><span class="glyphicon glyphicon-time"></span> Timetable</h3>
            </div>
            <div class="panel-body">
                <table class='table'>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Event Name</th>
                            <th>Session Slot ID</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="sortable">
                        <tr>
                            <td class="handler"><span class="glyphicon glyphicon-sort"></span></td>
                            <td>2</td>
                            <td>End</td>
                            <td>Event Name</td>
                            <td>Session Slot ID</td>
                            <td class="text-right">Action</td>
                        </tr>
                        <tr>
                            <td class="handler"><span class="glyphicon glyphicon-sort"></span></td>
                            <td>1</td>
                            <td>End</td>
                            <td>Event Name</td>
                            <td>Session Slot ID</td>
                            <td class="text-right">Action</td>
                        </tr>
                        <tr>
                            <td class="handler"><span class="glyphicon glyphicon-sort"></span></td>
                            <td>3</td>
                            <td>End</td>
                            <td>Event Name</td>
                            <td>Session Slot ID</td>
                            <td class="text-right">Action</td>
                        </tr>
                    </tbody>
                    <tr>
                        <td></td>
                        <td><input type="text" class="form-control" placeholder="xx:xx (24hr)"></td>
                        <td><input type="text" class="form-control" placeholder="xx:xx (24hr)"></td>
                        <td><input type="text" class="form-control" placeholder=""></td>
                        <td><input type="text" class="form-control" placeholder="integer, -1 if not session"></td>
                        <td class="text-right"><button class='btn btn-default'><span class="glyphicon glyphicon-plus"></span></button></td>
                    </tr>
                </table>
                <div class="text-right">
                    <button class='btn btn-success'>Save & Publish</button>
                </div>
            </div>
        </div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title large"><span class="glyphicon glyphicon-time"></span> Sessions</h3>
            </div>
            <div class="panel-body">

            </div>
        </div>
    <?php } ?>
</div>

<?php
    include "parts/footer.php";
?>

<script>
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

$(function(){
    $(".sortable").sortable();
})
</script>
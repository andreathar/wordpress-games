<div>
    <?php include_once("widget.urheader.php"); ?>

    <div id="ur-main-welcome">

        <div class="ur-big-center-title">
            Hello! What do you want to do today?
        </div>

        <div class="buttons">

            <div class="area" onclick="URMAIN.goTo(1)">
                <div class="icon" style="color:#2874a6"><i class="fa-solid fa-database fa-4x"></i></div>
                <div class="title">Manage my<br><b>DATABASE</b></div>
            </div>

            <div class="area" onclick="URMAIN.goTo(2)">
                <div class="icon" style="color:#117a65"><i class="fa-solid fa-layer-group fa-4x"></i></div>
                <div class="title">Manage my<br><b>APIs</b></div>
            </div>

            <div class="area" onclick="URMAIN.goTo(3)">
                <div class="icon" style="color:#b9770e"><i class="fa-solid fa-cloud-arrow-up fa-4x"></i></div>
                <div class="title">Manage<br><b>UPLOADS</b></div>
            </div>

            <div class="area" onclick="URMAIN.goTo(4)">
                <div class="icon" style="color:#283747"><i class="fa-brands fa-unity fa-4x"></i></div>
                <div class="title">Connect to<br><b>UNITY</b></div>
            </div>

        </div>

        <div class="buttons" style="margin-top:20px;">

            <div class="area" style="flex-direction:row;width:300px;height:auto;" onclick="URMAIN.unirestRefresh()">
                <div class="icon" style="color:#7b241c;width:auto;height:auto;"><i class="fa-solid fa-arrows-rotate fa-2x"></i></div>
                <div class="title" style="text-align:left;margin-left:20px;">Refresh my<br><b>UniREST SYSTEM</b></div>
            </div>

        </div>

        <div class="buttons" style="margin-top:60px;">

            <div class="footer">
                <div style="font-weight:bold;font-size:16px;">PHP CONFIGURATION</div>
                <div style="font-style:italic;">Your PHP configuration (php.ini) defines limits on the size of files you can upload, as well as the amount of data that can be sent or received via APIs.</div>
                <div style="margin-top:10px;" id="phpconfig"></div>
            </div>

        </div>

    </div>

    <div id="ur-main-first-start">

        <div class="ur-big-center-title">
            Welcome! I'm ready to start!
        </div>

        <div class="instruction">
            <span>FIRST START</span><br><br>
            <span>This is the first time you run the <b style="font-weight:bold;">UniREST WordPress plugin</b>.</span>
            <br>
            <span>I need to perform a <b style="font-weight:bold;">system check</b> to ensure that everything is properly set to use <b style="font-weight:bold;">UniREST</b>.</span>
            <br><br>
            <span><i>Click the [START CHECK] button below to start the system check.</i></span>
            <br>
            <br>
            <button class="ur-btn ur-btn-blue" onclick="URMAIN.systemCheck()">SYSTEM CHECK</button>
        </div>

    </div>

    <div id="ur-main-system-check">

        <div class="ur-big-center-title">
            Here is your system check!
        </div>

        <div class="instruction">
            <span>CHECK RESULTS</span><br><br>
            <span>The following list shows if your system meets the <b>UniREST</b> requirements.</span>
            <br>
            <span>If any issues have been detected, it is important that you resolve them before using <b>UniREST</b>.</span>
            <br>
            <br>

            <table id="ur-main-check-results">
                <tr>
                    <td><b>RESULT</b></td>
                    <td><b>DESCRIPTION</b></td>
                </tr>
            </table>

            <div id="ur-main-check-ok">
                <br>
                <i>Your system seems to be working fine and can be used with <b>UniREST</b>.<br>Click the [CONTINUE] button below to activate the <b>UniREST</b> plugin.</i>
                <br><br>
                <button class="ur-btn ur-btn-blue" onclick="URMAIN.install()">CONTINUE</button>
            </div>
            <div id="ur-main-check-ko">
                <br>
                Issues have been detected and need to be fixed in order to use <b>UniREST</b>. Please fix these issues and repeat the system check procedure.
            </div>

        </div>

    </div>

</div>

<script>
    URMAIN.onLoad();
</script>
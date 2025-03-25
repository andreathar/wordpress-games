<div>

    <?php include_once "widget.urheader.php";?>

    <h1 class="wp-heading-inline">SETTINGS</h1>
    <hr class="wp-header-end">

    <div id="ur-settings">

        <div class="box">
            <div class="title"><div class="icon"><i class="fa-solid fa-user-gear"></i></div>APPLICATION ACCOUNT</div>
            <div class="body" style="border-bottom:1px solid #CCC;background:white;">
            <div style="margin-bottom:4px;color:#2271b1;"><i>If your Unity application does not require individual user accounts, you can use the AppLogin() method to authenticate the application itself.</i></div>
            <div>
                <ul style="list-style:disc;margin-left: 20px;">
                    <li>In the Users section, create a dedicated account for your application, noting the username and password. Use complex and secure credentials to ensure the account is well-protected.</li>
                    <li>In the username and password fields below, enter the credentials of the dedicated account for your application.</li>
                    <li>Click [SAVE] to activate your Application Account.</li>
                    <li>In UniREST, generate the updated <b>Unity API Script</b> to integrate into your Unity project.</li>
                </ul>
            </div>
        </div>
        <div class="body">            
            <div style="font-size:14px;font-weight:bold;">Application Account Credentials</div>
            <div>Type the credentials in the fields below.</div>
            <input type="text" id="application-username" placeholder="Application Username" autocomplete="off">
            <input type="password" id="application-password" placeholder="Application Password" autocomplete="off">
            <br>
            <div style="color:darkred;margin-top:4px;">For security reasons, the credentials won't be displayed in the fields above in the future and will always remain empty.</div>
            <br>

            <button class="ur-btn ur-btn-blue" style="width: 100px;font-weight: bold;margin-right:10px;" onclick="URSETTINGS.saveAppAccount()">SAVE</button>

            </div>
        </div>

    </div>

    <div id="ur-settings">

        <div class="box">
            <div class="title"><div class="icon"><i class="fa-solid fa-link fa-2x"></i></div>WEBGL RELATIVE URL</div>
            <div class="body" style="border-bottom:1px solid #CCC;background:white;">
            <div style="margin-bottom:4px;color:#2271b1;"><i>The Unity WebGL platform generates an application that runs directly on the server. 
                <br>UniREST must be installed on the same server and use relative URLs instead of absolute URLs (which are, instead, required for platforms like Android and iOS).
        <br>
        A relative path cannot be automatically detected because it depends on where both the WebGL application and WordPress are installed.</i></div>
            <div>Enter the correct relative path from the WebGL application to the WordPress Uploads folder (for example, "../wp-content/uploads").</div>
        </div>
        <div class="body">            
            <div style="font-size:14px;font-weight:bold;">Relative Path</div>
            <div>It must point from the WebGL application to the WordPress Uploads folder.</div>
            <div><input id="webglpath" type="text" style="width:100%;"></div>
            <br>

            <button class="ur-btn ur-btn-blue" style="width: 100px;font-weight: bold;margin-right:10px;" onclick="URSETTINGS.saveWebGL()">SAVE</button>

            </div>
        </div>

    </div>

    <div id="ur-settings">

        <div class="box">
            <div class="title"><div class="icon"><i class="fa-solid fa-key fa-2x"></i></div>SECURITY KEYS</div>
            <div class="body" style="border-bottom:1px solid #CCC;background:white;">
            <div style="margin-bottom:4px;color:#2271b1;"><i>The two keys below enable the security system that ensures your data is securely exchanged between this Server and your Unity project.
            These keys must be kept secret, and it is highly recommended to copy them somewhere safe to have a backup.</i></div>
            <div>If you install a new copy of the UniREST plugin, new keys will be generated, and an existing Unity project will no longer be able to communicate with the server. 
            However, these keys can be modified and overwritten with keys previously used.</div>
        </div>
        <div class="body">            
            <div style="font-size:14px;font-weight:bold;">KEY 1</div>
            <div>It must be 32 characters long.</div>
            <div><input id="key1" type="text" style="width:100%;"></div>
            <br>
            <div style="font-size:14px;font-weight:bold;">KEY 2</div>
            <div>It must be 16 characters long.</div>
            <div><input id="key2" type="text" style="width:100%;"></div>

            <br>

            <button class="ur-btn ur-btn-blue" style="width: 100px;font-weight: bold;margin-right:10px;" onclick="URSETTINGS.saveKeys()">SAVE</button>
            <button class="ur-btn ur-btn-red" style="width: 100px;font-weight: bold;" onclick="URSETTINGS.newKeys()">REGENERATE</button>

            </div>
        </div>

    </div>

</div>

<script>
    URSETTINGS.onLoad();
</script>
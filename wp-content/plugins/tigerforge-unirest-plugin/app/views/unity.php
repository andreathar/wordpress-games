<div>

    <?php include_once "widget.urheader.php";?>

    <h1 class="wp-heading-inline">UNITY API SCRIPT</h1>
    <a href="#" onclick='URUNITY.generate()' class="page-title-action">GENERATE</a>
    <hr class="wp-header-end">

    <div style="position:relative;">
        <div>Click the [GENERARE] button. Then, copy the Unity API Script in your Unity projet, into the <b>UniRESTAPIScript.cs</b> file.
            <br>This operation will also create or update the <b>Unity API system</b>.
        </div>
        <br>
        <div id="ur-script-container">
            <div id="ur-script-instructions" class="instructions">
                <div>INSTRUCTIONS</div>
                <div>• Click the [GENERATE] button above.</div>
                <div>• Copy the generated C# Script.</div>
                <div>• In your Unity project, locate the <b style="color: #ffcd72;">UniRESTAPIScript.cs</b> file (it's in the UniREST asset folder).</div>
                <div>• Paste the generated C# Script in that file.</div>
            </div>
            <div id="ur-unity-script" style="height:600px;"></div>
        </div>
        <div onclick="URUNITY.copy()" id="ur-copy-btn" class="ur-copy-btn"><i class="fa-regular fa-copy fa-2x"></i></div>
        <div style="margin-top:10px;">
            <b>WHEN TO GENERATE</b><br>
            • every time you modify an API;<br>
            • every time you modify a Database Table;<br>
            • every time you modify a UniREST plugin setting;<br>
            • every time you update the UniREST plugin.              
        </div>
    </div>

</div>

<script>
     URUNITY.onStart();
</script>
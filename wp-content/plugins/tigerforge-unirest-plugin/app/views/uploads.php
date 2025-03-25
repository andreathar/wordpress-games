<div>

    <?php include_once "widget.urheader.php";?>

    <h1 class="wp-heading-inline">UPLOADS</h1>
    <hr class="wp-header-end">

    <div id="ur-uploads">

        <div class="user-id">
            <div style="font-weight:bold;">User</div>
            <div style="font-style:italic;;margin-bottom:6px;">Type a user's ID, Username or E-mail address</div>
            <input type="text" id="userInput" placeholder="User ID, Username or Email">
            <button class="ur-btn ur-btn-blue" style="width: 100px;font-weight: bold;" onclick="URUPLOADS.getUserData();">SHOW</button>
        </div>

        <table id="fileTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">No files to display</td>
                </tr>
            </tbody>
        </table>

    </div>

</div>

<script>
    URUPLOADS.onLoad();
</script>
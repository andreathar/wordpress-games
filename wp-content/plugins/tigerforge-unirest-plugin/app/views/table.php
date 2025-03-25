<?php
include_once "widget.urheader.php";

$name = $_GET["name"];
?>

<h1 class="wp-heading-inline"><i class="ur-back-btn fa-regular fa-circle-left" onclick="history.back()"></i> <i class="fa-solid fa-table"></i> <?=$name?></h1>
<a href="#" onclick='URTABLE.insertRecord()' class="page-title-action">Insert new Record</a>
<hr class="wp-header-end">

<div id="urtableheader">
    <div>
        <button class="ur-btn ur-btn-blue" style="height:30px;padding:0px 10px;" data-bind="click: prevPage, enable: currentPage() > 1">Back</button>
        <span data-bind="text: currentPage"></span> /
        <span data-bind="text: totalPages"></span>
        <button class="ur-btn ur-btn-blue" style="height:30px;padding:0px 10px;" data-bind="click: nextPage, enable: currentPage() < totalPages()">Next</button>
    </div>
    <div style="display: flex;align-items: center;">
        Jump to page 
        <select id="jumpToPage" data-bind="event: { change: changePage }, foreach: pageNumbers">
            <option data-bind="value: $data, text: $data"></option>
        </select>
    </div>
    <div style="display: flex;align-items: center;margin-left: auto;">
        <label for="recordsPerPage">Records per page:</label>
        <select id="recordsPerPage" data-bind="value: recordsPerPage, event: { change: fetchRecords }">
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="200">200</option>
            <option value="500">500</option>
        </select>
    </div>    
</div>

<table id="urtablerecords">
    <thead>
        <tr data-bind="foreach: columns">
            <td><span data-bind="text: name"></span><br>[<span data-bind="text: type"></span>]</td>
            <td></td>
        </tr>
    </thead>
    <tbody data-bind="foreach: records">
        <tr data-bind="foreach: $data">
            <td field data-bind="
            text: $data === null ? '[NULL]' : ($data.length > 20 ? $data.substring(0, 20) + '...' : $data), 
            attr: {title: $data === null ? '[NULL]' : ($data.length > 200 ? $data.substring(0, 200) + '...' : $data)}
            "></td>
            <td data-bind="if: $index() == $parent.length - 1 && $parent[0] != ''">
                <span onclick='URTABLE.action(this, "EDIT")' style="cursor:pointer;"><i class="fa-solid fa-pen-to-square"></i> EDIT</span> &nbsp;&nbsp;&nbsp;&nbsp;
                <span onclick='URTABLE.action(this, "DELETE")' style="color:darkred;cursor:pointer;"><i class="fa-solid fa-trash"></i> DELETE</span>
            </td>
        </tr>
    </tbody>
</table>

<div id="urtablefooter">
    <div>
        <button class="ur-btn ur-btn-blue" style="height:30px;padding:0px 10px;" data-bind="click: prevPage, enable: currentPage() > 1">Back</button>
        <span data-bind="text: currentPage"></span> /
        <span data-bind="text: totalPages"></span>
        <button class="ur-btn ur-btn-blue" style="height:30px;padding:0px 10px;" data-bind="click: nextPage, enable: currentPage() < totalPages()">Next</button>
    </div>
    <div style="display: flex;align-items: center;">
        Jump to page 
        <select id="jumpToPage" data-bind="event: { change: changePage }, foreach: pageNumbers">
            <option data-bind="value: $data, text: $data"></option>
        </select>
    </div>
</div>




<script>
    URTABLE.tableName = "<?=$name?>";
    URTABLE.onStart();
</script>

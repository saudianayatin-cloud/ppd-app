<?php
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 0);   // expires when browser closes
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>PPD-PIMS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="images/mpw-icon.png">

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

    <!-- DataTables CSS + JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap 4 CSS + JS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Font Awesome (for icons instead of glyphicon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">



    <style>
        .container {
            margin: 0 auto;
            text-align: center;
            max-width: 1800px;
        }

        table {
            margin: 0 auto;
            border-collapse: collapse;
        }

        table.dataTable {
            border: 1px solid #ccc;
        }

        div.dataTables_filter {
            margin-bottom: 5px;
        }

        table.dataTable th,
        table.dataTable td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .files-explorer-shell {
            border: 1px solid #d7dce2;
            border-radius: 4px;
            overflow: hidden;
            background: #fff;
        }

        .files-explorer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 10px;
            background: #f5f7fa;
            border-bottom: 1px solid #d7dce2;
        }

        .files-explorer-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #38424f;
        }

        .files-explorer-actions {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .files-icon-btn {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: #38424f;
        }

        .files-icon-btn:hover {
            background: #e7ebf0;
            color: #0a376e;
        }

        .files-icon-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .files-upload-panel {
            padding: 12px;
            border-bottom: 1px solid #e4e7eb;
        }

        .files-target-label {
            font-size: 12px;
            color: #607080;
        }

        .files-target-label strong {
            color: #28323d;
        }

        .files-explorer-body {
            min-height: 260px;
            max-height: 430px;
            overflow: auto;
            padding: 6px;
        }

        .files-tree-row {
            display: flex;
            align-items: center;
            min-height: 34px;
            gap: 8px;
            padding: 5px 8px;
            border-radius: 4px;
            color: #26313d;
        }

        .files-tree-row:hover,
        .files-tree-row.is-selected {
            background: #eaf3ff;
        }

        .files-tree-row.is-drag-over,
        .files-explorer-body.is-drag-over {
            background: #d8ecff;
            outline: 2px dashed #2584d8;
            outline-offset: -2px;
        }

        .files-tree-row.is-cut {
            opacity: .55;
            background: #fff8df;
        }

        .files-tree-row.is-folder {
            padding-left: calc(8px + (var(--tree-depth, 0) * 24px));
        }

        .files-tree-row.is-file {
            padding-left: calc(36px + (var(--tree-depth, 0) * 24px));
        }

        .files-tree-main {
            flex: 1;
            min-width: 0;
        }

        .files-tree-name {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
        }

        .files-tree-meta {
            display: block;
            font-size: 12px;
            color: #6b7785;
        }

        .files-tree-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex: 0 0 auto;
        }

        .files-empty-state {
            padding: 26px 12px;
            text-align: center;
            color: #6b7785;
            font-style: italic;
        }
    </style>
</head>

<body>




    <?php include 'navbaradmin.php' ?>
    <br>


    <div class="container">
        <!-- <?php echo "<a href='logout_docs_user.php' style='float:right; margin-right:10px;'>Logout</a>"; ?> -->
        <h3 style="text-align: left; color: #0a376e;">Funded List</h3>
        <a href="" style="align-items: right;"></a>
        <!-- Filter -->
        <!-- Filter -->
        <div style="text-align: left;">
            <label for="deo-filter">Filter by DEO:</label>
            <select id="deo-filter" class="form-control d-inline-block" style="width: auto;">
                <option value="">All</option>
            </select>
        </div>

        <!-- REL Filter Dropdown -->
        <!-- <div class="mb-3">
            <label for="rel-filter" class="form-label fw-bold">Filter by DIVISION:</label>
            <select id="rel-filter" class="form-select" style="max-width:300px;">
                <option value="">All</option>
                <option value="PLANNING DIVISION">PLANNING DIVISION</option>
                <option value="PROGRAMMING DIVISION">PROGRAMMING DIVISION</option>
                <option value="E-BARMM UNIT">E-BARMM UNIT</option>
            </select>
        </div> -->


        <!-- Add button -->

        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#form_modal">
                <i class="fas fa-plus"></i> Add
            </button>
        </div>

        <div id="user-message" class="alert" style="display:none;"></div>
        <!-- DataTable -->
        <table id="your-table" class="display" style="width:100%"></table>
    </div>

    <!-- Add Document Modal -->
    <div class="modal fade" id="form_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- <form id="add_document_form" method="POST" action="save_documents_cside_funded.php" enctype="multipart/form-data"> -->
                <form id="addDocsForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fas fa-plus"></i> New Project</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label>District Engineering Office:</label>
                            <select name="deo" class="form-control" required>
                                <option value="">District Engineering Office</option>
                                <option value="BAS">Basilan</option>
                                <option value="SUL1">Sulu District 1</option>
                                <option value="SUL2">Sulu District 2</option>
                                <option value="TAW">Tawi-Tawi</option>
                                <option value="MDN">Maguindanao Del Norte</option>
                                <option value="MDS">Maguindanao Del Sur</option>
                                <option value="LDS1">Lanao Del Sur District 1</option>
                                <option value="LDS2">Lanao Del Sur District 2</option>
                                <option value="SGA">Special Geographic Area</option>
                                <option value="COT">Cotabato City</option>
                                <option value="OSB">Outside Barmm</option>
                            </select>
                        </div>



                        <div class="form-group">
                            <label>Project Code:</label>
                            <input class="form-control" name="stud_no"
                                oninput="this.value = this.value.replace(/ /g, '')">

                        </div>

                        <div class="form-group">
                            <label for="">Project Name:</label>
                            <textarea class="form-control" name="proj_name" rows="2" required="required"></textarea>
                        </div>


                        <div class="form-group">
                            <label for="">Municipality:</label>
                            <textarea class="form-control" name="municipality" rows="1" required="required"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Barangay:</label>
                            <textarea class="form-control" name="barangay" rows="1"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Purok/Sitio:</label>
                            <textarea class="form-control" name="sitio" rows="1"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="">Fiscal Year:</label>
                            <select name="cy" class="form-control" required>
                                <option value="">FY</option>
                                <option value="2012">2012</option>
                                <option value="2013">2013</option>
                                <option value="2014">2014</option>
                                <option value="2015">2015</option>
                                <option value="2016">2016</option>
                                <option value="2017">2017</option>
                                <option value="2018">2018</option>
                                <option value="2019">2019</option>
                                <option value="2020">2020</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                                <option value="2028">2028</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Fund Source:</label>
                            <select name="fund_source" class="form-control" required>
                                <option value="">Fund Source</option>
                                <option value="RegularInfra">RegularInfra</option>
                                <option value="TDIF">TDIF</option>
                                <option value="SDF">SDF</option>
                                <option value="Contingency Fund">CONTINGENCY FUND</option>
                                <option value="Supplemental Fund">SUPPLEMENTAL FUND</option>
                                <option value="OPPAP">OPPAP</option>
                                <option value="DA-FMR">DA-FMR</option>
                                <option value="PAMANA-DILG">PAMANA-DILG</option>
                                <option value="ARMM-HELPS">ARMM-HELPS</option>
                                <option value="DepEd ARMM">DepEd ARMM</option>
                                <option value="GAAB">GAAB</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Mode of Implementation:</label>
                            <select name="moi" class="form-control">
                                <option value=" ">Mode Of Implementation</option>
                                <option value="byAdmin">byAdmin</option>
                                <option value="MOA">MOA</option>
                                <option value="byContract">byContract</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">Project Length (Km/Unit):</label>
                            <input class="form-control" name="proj_target" rows="1">
                        </div>
                        <div>
                            <label for="">Project Scale (As Program):</label>
                            <input class="form-control" name="proj_plan" rows="1">
                        </div>
                        <div>
                            <label for="">Appropriation:</label>
                            <input class="form-control" name="cost" rows="1">
                        </div>


                        <div class="form-group">
                            <label class="required">Proponent:</label>
                            <textarea class="form-control" name="proponent" rows="1"></textarea>
                        </div>
                        <!-- 
                        <div class="form-group" hidden>
                            <label class="required">Status:</label>
                            <textarea class="form-control" name="status" rows="2"></textarea>
                        </div> -->
                        <!-- <div class="form-group" hidden>
                            <label class="required">Regional Office Remarks:</label>
                            <textarea class="form-control" name="ro_remarks" rows="2"></textarea>
                        </div> -->
                        <!-- <div class="form-group" hidden>
                            <label class="required">Distruct Engineering Office Remarks:</label>
                            <textarea class="form-control" name="deo_remarks" rows="2"></textarea>
                        </div> -->


                        <div class="form-group">
                            <label>Attach Document (pdf/Image)</label>
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <!-- Upload Progress UI -->
                        <div id="uploadProgressContainerAdd" style="display:none;">
                            <div class="progress mt-3 oneui-progress">
                                <div id="uploadProgressBarAdd" class="progress-bar" style="width:0%"></div>
                            </div>

                            <div class="text-center mt-2" id="uploadPercentTextAdd">0%</div>
                            <div class="text-center small text-muted" id="uploadStatsAdd">0 MB / 0 MB • ETA: --</div>
                        </div>

                        <div id="uploadLoaderAdd"></div>

                        <!-- Cancel Upload Button -->
                        <div class="text-center mt-2" style="display:none;" id="cancelUploadAddContainer">
                            <button type="button" class="btn btn-warning btn-sm" id="cancelUploadAddBtn">
                                ✖ Cancel Upload
                            </button>
                        </div>

                        <!-- ✅ Progress Bar -->
                        <div id="uploadProgressContainerAdd" style="display:none;">
                            <div class="progress position-relative mt-3">
                                <div id="uploadProgressBarAdd" class="progress-bar" role="progressbar" style="width:0%">
                                    <span id="uploadPercentTextAdd">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {

            let addXhr = null;
            let uploadCanceledAdd = false;



            $('#addDocsForm').on('submit', function(e) {
                e.preventDefault();

                //add
                const fileInput = $('input[name="file"]')[0];
                const hasFile = fileInput.files && fileInput.files.length > 0;


                //message function
                function showMessage(type, text, duration = 300) {
                    const box = $('#user-message');

                    box.stop(true, true) // 🔥 cancels queued animations
                        .removeClass('alert-success alert-danger')
                        .addClass(type === 'success' ? 'alert-success' : 'alert-danger')
                        .text(text)
                        .fadeIn(40)
                        .delay(100)
                        .fadeOut(40);
                }



                uploadCanceledAdd = false;
                let formData = new FormData(this);

                $.ajax({
                    url: 'save_documents_cside_funded.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    xhr: function() {
                        addXhr = new window.XMLHttpRequest();

                        // 🚨 IMPORTANT: only attach upload handlers IF file exists
                        if (!hasFile) {
                            return addXhr;
                        }

                        let lastTime = Date.now();
                        let lastLoaded = 0;
                        let speedSamples = [];

                        addXhr.upload.addEventListener("loadstart", function() {
                            $("#uploadLoaderAdd").fadeIn(150);
                            $("#uploadProgressContainerAdd").fadeIn(200);
                            $("#cancelUploadAddContainer").fadeIn(200);
                        });

                        addXhr.upload.addEventListener("progress", function(e) {
                            if (!e.lengthComputable) return;

                            let percent = Math.round((e.loaded / e.total) * 100);
                            let loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                            let totalMB = (e.total / (1024 * 1024)).toFixed(2);

                            $("#uploadProgressBarAdd").css("width", percent + "%");

                            let now = Date.now();
                            let timeDiffSec = (now - lastTime) / 1000;
                            let bytesDiff = e.loaded - lastLoaded;

                            lastTime = now;
                            lastLoaded = e.loaded;

                            let mbps = (bytesDiff * 8) / (1024 * 1024) / timeDiffSec;
                            speedSamples.push(mbps);
                            if (speedSamples.length > 5) speedSamples.shift();

                            let avgMbps = (
                                speedSamples.reduce((a, b) => a + b, 0) / speedSamples.length
                            ).toFixed(2);

                            let remainingBytes = e.total - e.loaded;
                            let remainingSeconds = avgMbps > 0 ?
                                (remainingBytes * 8) / (avgMbps * 1024 * 1024) :
                                0;

                            let etaText = remainingSeconds > 0 ? Math.ceil(remainingSeconds) + "s" : "--";

                            $("#uploadPercentTextAdd").text(`${percent}% – ${avgMbps} Mbps`);
                            $("#uploadStatsAdd").text(`${loadedMB} MB / ${totalMB} MB • ETA: ${etaText}`);

                            if (percent >= 100) {
                                $("#uploadPercentTextAdd").text("Processing…");
                                $("#uploadStatsAdd").text(`${totalMB} MB / ${totalMB} MB • ETA: 0s`);
                                $("#uploadLoaderAdd").hide();
                            }
                        });

                        addXhr.addEventListener("abort", function() {
                            if (!uploadCanceledAdd) return;

                            $("#uploadLoaderAdd").hide();
                            $("#uploadPercentTextAdd").text("Upload Cancelled");
                            $("#uploadStatsAdd").text("0 MB / 0 MB • ETA: --");
                            $("#uploadProgressBarAdd").css("width", "0%");
                        });

                        return addXhr;
                    },


                    success: function(response) {
                        if (uploadCanceledAdd) return;

                        const msgBox = $('#user-message');
                        showMessage(
                            response.status === 'success' ? 'success' : 'error',
                            response.message,
                            225 // 👈 stays visible ~0.225s
                        );


                        $("#cancelUploadAddContainer").fadeOut(100);

                        if (response.status === 'success') {

                            setTimeout(() => {
                                $("#uploadProgressContainerAdd").fadeOut(150, function() {
                                    $("#uploadProgressBarAdd").css("width", "0%");
                                    $("#uploadPercentTextAdd").text("0%");
                                    $("#uploadStatsAdd").text("0 MB / 0 MB • ETA: --");
                                });
                            }, 450);


                            setTimeout(() => {
                                // msgBox.fadeOut();
                                $('#form_modal').modal('hide');
                                $('#addDocsForm')[0].reset();
                                $('#your-table').DataTable().ajax.reload();

                            }, 200);
                        }
                    },

                    error: function() {
                        if (uploadCanceledAdd) return;

                        $("#uploadLoaderAdd").hide();
                        $("#cancelUploadAddContainer").fadeOut(100);
                        showMessage('error', 'An unexpected error occurred.', 1000);

                    }
                });
            });

            // CANCEL BUTTON CLICK
            $('#cancelUploadAddBtn').on('click', function() {
                uploadCanceledAdd = true;

                if (addXhr) {
                    addXhr.abort();
                }

                $("#uploadProgressContainerAdd").fadeOut(150);
                $("#cancelUploadAddContainer").fadeOut(150);
            });

        });
    </script>





    <style>
        /* One UI style gradient progress bar */
        .oneui-progress {
            height: 22px;
            border-radius: 50px;
            overflow: hidden;
            background: #ebebeb;
        }

        .oneui-progress .progress-bar {
            background: linear-gradient(90deg, #4c8df6, #2563eb, #1e40af);
            background-size: 300% 100%;
            animation: gradientMove 3s ease infinite;
            transition: width 0.25s ease;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 0%;
            }

            50% {
                background-position: 100% 0%;
            }

            100% {
                background-position: 0% 0%;
            }
        }

        /* Spinning Loader */
        #uploadLoaderAdd {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #007bff;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            margin: 10px auto;
            animation: spin 1.2s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>








    <!-- View Document Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Location Map Preview</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body" style="height:80vh;">
                    <!-- Iframe for document -->
                    <iframe id="docFrame" style="width:100%; height:100%; display:none;" frameborder="0"></iframe>

                    <!-- Message when no file -->
                    <div id="docMessage" class="text-center p-3" style="display:none; font-style:italic; color:gray;">
                        📂 No file uploaded yet.
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- Project Files Modal -->
    <div class="modal fade" id="filesModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="fundedFilesForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title">Project Files</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="funded_id" id="filesFundedId">
                        <input type="hidden" name="folder_id" id="filesFolderId">
                        <input type="hidden" name="action" value="upload">

                        <div id="filesAlert" class="alert" style="display:none;"></div>

                        <div class="files-explorer-shell">
                            <div class="files-explorer-bar">
                                <h5 class="files-explorer-title">Files</h5>
                                <div class="files-explorer-actions">
                                    <button type="button" class="files-icon-btn" id="filesNewFileBtn" title="New File">
                                        <i class="fas fa-file-medical"></i>
                                    </button>
                                    <button type="button" class="files-icon-btn" id="filesNewFolderBtn" title="New Folder">
                                        <i class="fas fa-folder-plus"></i>
                                    </button>
                                    <button type="button" class="files-icon-btn" id="filesRefreshBtn" title="Refresh Explorer">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button type="button" class="files-icon-btn" id="filesCollapseBtn" title="Collapse Folders">
                                        <i class="fas fa-compress-alt"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="files-upload-panel">
                                <div class="form-group mb-2">
                                    <label>Attach Files</label>
                                    <input type="file" class="form-control" name="files[]" id="fundedFilesInput" multiple>
                                    <input type="file" name="files[]" id="fundedFoldersInput" multiple webkitdirectory directory style="display:none;">
                                    <div class="mt-2" style="display:none;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="filesChooseFolderBtn">
                                            <i class="fas fa-folder-open"></i> Choose Folder
                                        </button>
                                    </div>
                                    <small class="text-muted">You can select multiple files at once.</small>
                                </div>

                                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
                                    <div class="files-target-label">Upload target: <strong id="filesUploadTarget">Project root</strong></div>
                                    <button type="submit" class="btn btn-primary btn-sm" id="filesUploadBtn">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </div>

                                <div id="filesUploadProgressContainer" class="mt-3" style="display:none;">
                                    <div class="progress">
                                        <div id="filesUploadProgressBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                                    </div>
                                    <div class="text-center mt-2" id="filesUploadPercentText">0%</div>
                                    <div class="text-center small text-muted" id="filesUploadStats">0 MB / 0 MB</div>
                                </div>
                            </div>

                            <div id="fundedFilesExplorer" class="files-explorer-body"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>














    <div class="modal fade" id="edit_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="update_documents_cside_funded.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title"><span class="glyphicon glyphicon-edit"></span> Update</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="stud_id2" id="stud_id2">


                        <div class="form-group">
                            <label>District Engineering Office</label>
                            <select name="deo" id="deo" class="form-control" required="required">
                                <option value="BAS">Basilan</option>
                                <option value="SUL1">Sulu District 1</option>
                                <option value="SUL2">Sulu District 2</option>
                                <option value="TAW">Tawi-Tawi</option>
                                <option value="MDN">Maguindanao Del Norte</option>
                                <option value="MDS">Maguindanao Del Sur</option>
                                <option value="LDS1">Lanao Del Sur District 1</option>
                                <option value="LDS2">Lanao Del Sur District 2</option>
                                <option value="SGA">Special Geographic Area</option>
                                <option value="COT">Cotabato City</option>
                                <option value="OSB">Outside Barmm</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label> Project Code</label>
                            <input type="text" class="form-control" name="stud_no" id="stud_no">
                        </div>


                        <div class="form-group">
                            <label>Project Name</label>
                            <textarea class="form-control" name="proj_name" id="proj_name" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Municipality</label>
                            <input class="form-control" id="municipality" name="municipality">
                        </div>

                        <div class="form-group">
                            <label>Barangay</label>
                            <input class="form-control" id="barangay" name="barangay">
                        </div>

                        <div class="form-group">
                            <label>Sitio</label>
                            <input class="form-control" id="sitio" name="sitio">
                        </div>


                        <div class="form-group">
                            <label>FY</label>
                            <select name="cy" id="cy" class="form-control">
                                <option value="2012">2012</option>
                                <option value="2013">2013</option>
                                <option value="2014">2014</option>
                                <option value="2015">2015</option>
                                <option value="2016">2016</option>
                                <option value="2017">2017</option>
                                <option value="2018">2018</option>
                                <option value="2019">2019</option>
                                <option value="2020">2020</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                                <option value="2028">2028</option>
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Fund Source</label>
                            <select name="fund_source" id="fund_source" class="form-control">
                                <option value="RegularInfra">RegularInfra</option>
                                <option value="TDIF">TDIF</option>
                                <option value="SDF">SDF</option>
                                <option value="Contingency Fund">CONTINGENCY FUND</option>
                                <option value="Supplemental Fund">SUPPLEMENTAL FUND</option>
                                <option value="OPPAP">OPPAP</option>
                                <option value="DA-FMR">DA-FMR</option>
                                <option value="PAMANA-DILG">PAMANA-DILG</option>
                                <option value="ARMM-HELPS">ARMM-HELPS</option>
                                <option value="DepEd ARMM">DepEd ARMM</option>
                                <option value="GAAB">GAAB</option>
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Mode Of Implementation</label>
                            <select name="moi" id="moi" class="form-control">
                                <option value="byAdmin">byAdmin</option>
                                <option value="MOA">MOA</option>
                                <option value="byContract">byContract</option>
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Project Length (Km/Unit)</label>
                            <input class="form-control" name="proj_target" id="proj_target">
                        </div>

                        <div class="form-group">
                            <label>Project Scale (As Plan)</label>
                            <input class="form-control" name="proj_plan" id="proj_plan">
                        </div>

                        <div class="form-group">
                            <label>Appropriation</label>
                            <input class="form-control" name="cost" id="cost">
                        </div>

                        <div class="form-group">
                            <label>Proponent</label>
                            <textarea class="form-control" name="proponent" id="proponent" rows="1"></textarea>
                        </div>

                        <!-- <div class="form-group" hidden>
                            <label>Status</label>
                            <textarea class="form-control" name="status" id="status" rows="3"></textarea>
                        </div> -->

                        <!-- <div class="form-group" hidden>
                            <label>RO Remarks</label>
                            <textarea class="form-control" name="ro_remarks" id="ro_remarks" rows="3"></textarea>
                        </div> -->

                        <!-- <div class="form-group" hidden>
                            <label>DEO Remarks</label>
                            <textarea class="form-control" name="deo_remarks" id="deo_remarks" rows="3"></textarea>
                        </div> -->


                        <div class="form-group">
                            <label for="file">File(*PDF/Image)</label>
                            <input type="file" class="form-control" name="file" id="file" accept=".pdf,.jpg,.jpeg,.png">
                            <small id="existing_file" class="text-muted"></small>
                        </div>

                        <!-- Progress Bar -->
                        <div class="position-relative mt-2" style="display: none;" id="uploadProgressContainer">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" id="uploadProgressBar"></div>
                            </div>
                            <div id="uploadPercentText">0%</div>
                        </div>

                        <!-- Cancel Upload Button -->
                        <div class="mt-2 text-center" style="display:none;" id="cancelUploadContainer">
                            <button type="button" class="btn btn-warning btn-sm" id="cancelUploadBtn">
                                ✖ Cancel Upload
                            </button>
                        </div>

                    </div>
                    <div style="clear:both;"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><span
                                class="glyphicon glyphicon-remove"></span> Close</button>
                        <button name="update" class="btn btn-success"><span class="glyphicon glyphicon-saved"></span>
                            Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <style>
        /* One UI style gradient progress bar */
        .progress {
            height: 28px;
            background-color: #d9d9d9;
            border-radius: 14px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            background: linear-gradient(90deg,
                    #6dd5ed,
                    #2193b0,
                    #6dd5ed);
            background-size: 300% 300%;
            animation: gradientMove 3s ease infinite;
            transition: width 0.25s ease;
            font-weight: bold;
            font-size: 14px;
            color: #fff;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Glow on success */
        .progress-bar.glow {
            box-shadow: 0 0 12px #00e676, 0 0 24px #00e676;
        }

        /* Centered text (percentage, speed, ETA) */
        #uploadPercentText {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            text-align: center;
            line-height: 28px;
            pointer-events: none;
        }

        /* Upload info (speed, ETA, MB uploaded) */
        #uploadInfo {
            font-size: 13px;
            margin-top: 5px;
            text-align: center;
            color: #555;
            font-weight: 500;
        }
    </style>




    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editForm = document.querySelector('#edit_modal form');
            if (!editForm) return;

            let xhr = null;
            let uploadCanceled = false;

            editForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const formData = new FormData(form);
                xhr = new XMLHttpRequest();

                const progressContainer = document.getElementById('uploadProgressContainer');
                const progressBar = document.getElementById('uploadProgressBar');
                const percentText = document.getElementById('uploadPercentText');
                const cancelContainer = document.getElementById('cancelUploadContainer');
                const cancelBtn = document.getElementById('cancelUploadBtn');
                const editedId = document.getElementById('stud_id2').value;

                // Info box
                let infoBox = document.getElementById("uploadInfo");
                if (!infoBox) {
                    infoBox = document.createElement("div");
                    infoBox.id = "uploadInfo";
                    progressContainer.appendChild(infoBox);
                }

                progressContainer.style.display = 'block';
                cancelContainer.style.display = 'block';
                progressBar.style.width = '0%';
                percentText.textContent = '0%';
                infoBox.innerHTML = '';
                progressBar.classList.remove('bg-danger', 'bg-success', 'glow');

                uploadCanceled = false;

                let startTime = Date.now();
                let lastLoaded = 0;

                // CANCEL BUTTON
                cancelBtn.onclick = function() {
                    uploadCanceled = true;
                    if (xhr) xhr.abort();

                    progressBar.classList.add('bg-danger');
                    percentText.textContent = 'Upload Cancelled';
                    infoBox.innerHTML = '<b>❌ Upload cancelled by user</b>';

                    setTimeout(() => {
                        progressContainer.style.display = 'none';
                        cancelContainer.style.display = 'none';
                        progressBar.style.width = '0%';
                        percentText.textContent = '0%';
                        progressBar.classList.remove('bg-danger', 'glow');
                        infoBox.innerHTML = '';
                    }, 1200);
                };

                // PROGRESS
                xhr.upload.addEventListener('progress', function(e) {
                    if (!e.lengthComputable) return;

                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    percentText.textContent = percent + '%';

                    const uploadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                    const totalMB = (e.total / (1024 * 1024)).toFixed(2);

                    const speedMbps = ((e.loaded - lastLoaded) * 8 / 1_000_000).toFixed(2);
                    lastLoaded = e.loaded;

                    const remaining = e.total - e.loaded;
                    const eta = speedMbps > 0 ?
                        Math.round((remaining * 8 / 1_000_000) / speedMbps) :
                        1;

                    infoBox.innerHTML = `
                <span>📤 ${uploadedMB} MB / ${totalMB} MB</span><br>
                <span>⚡ Speed: ${speedMbps} Mbps</span><br>
                <span>⏳ ETA: ${eta}s</span>
            `;
                });

                // SUCCESS
                xhr.addEventListener('load', function() {
                    if (uploadCanceled) return;

                    let res;
                    try {
                        res = JSON.parse(xhr.responseText);
                    } catch {
                        res = {
                            status: 'success'
                        };
                    }

                    if (res.status !== 'success') {
                        progressBar.classList.add('bg-danger');
                        percentText.textContent = 'Upload Failed';
                        cancelContainer.style.display = 'none';
                        return;
                    }

                    progressBar.classList.add('bg-success', 'glow');
                    progressBar.style.width = '100%';
                    percentText.textContent = 'Upload Complete ✓';
                    infoBox.innerHTML += '<br><b>✔ Successfully Updated</b>';
                    cancelContainer.style.display = 'none';

                    // Update DataTable
                    if ($.fn.DataTable.isDataTable('#your-table')) {
                        const table = $('#your-table').DataTable();

                        $.ajax({
                            url: 'get_single_document_funded.php',
                            method: 'GET',
                            data: {
                                id: editedId
                            },
                            dataType: 'json',
                            success: function(newData) {
                                if (newData && newData.stud_id2) {
                                    const row = table.row('#' + newData.stud_id2);
                                    row.node() ? row.data(newData).draw(false) :
                                        table.ajax.reload(null, false);
                                } else {
                                    table.ajax.reload(null, false);
                                }
                            },
                            complete: function() {
                                setTimeout(() => {
                                    $('#edit_modal').modal('hide');
                                    form.reset();
                                    progressContainer.style.display = 'none';
                                    progressBar.style.width = '0%';
                                    percentText.textContent = '0%';
                                    progressBar.classList.remove('bg-success', 'bg-danger', 'glow');
                                    infoBox.innerHTML = '';
                                }, 900);
                            }
                        });
                    } else {
                        location.reload();
                    }
                });

                // ERROR
                xhr.addEventListener('error', function() {
                    progressBar.classList.add('bg-danger');
                    percentText.textContent = 'Upload Failed';
                    cancelContainer.style.display = 'none';
                });

                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.send(formData);
            });
        });
    </script>








    <script>
        function openModal(rowData) {
            document.getElementById('stud_id2').value = rowData.stud_id2 || ''; // ✅ assign ID
            document.getElementById('deo').value = rowData.deo || '';
            document.getElementById('stud_no').value = rowData.stud_no || '';
            document.getElementById('proj_name').value = rowData.proj_name || '';
            document.getElementById('municipality').value = rowData.municipality || '';
            document.getElementById('barangay').value = rowData.barangay || '';
            document.getElementById('sitio').value = rowData.sitio || '';
            document.getElementById('cy').value = rowData.cy || '';
            const fundSourceSelect = document.getElementById('fund_source');
            const fundSourceValue = normalizeFundSource(rowData.fund_source || '');
            if (fundSourceValue && ![...fundSourceSelect.options].some(option => option.value === fundSourceValue)) {
                fundSourceSelect.add(new Option(fundSourceValue, fundSourceValue));
            }
            fundSourceSelect.value = fundSourceValue;
            document.getElementById('moi').value = rowData.moi || '';
            document.getElementById('proj_target').value = rowData.proj_target || '';
            document.getElementById('proj_plan').value = rowData.proj_plan || '';
            document.getElementById('cost').value = rowData.cost || '';
            document.getElementById('proponent').value = rowData.proponent || '';
            // document.getElementById('status').value = rowData.status || '';
            // document.getElementById('ro_remarks').value = rowData.ro_remarks || '';
            // document.getElementById('deo_remarks').value = rowData.deo_remarks || '';

            if (rowData.file) {
                document.getElementById('existing_file').innerHTML =
                    // `Current File: <a href="/admin/funded_uploads/${encodeURIComponent(rowData.file)}" target="_blank">${rowData.file}</a>`;
                    `Current File: ${rowData.file}`;
            } else {
                document.getElementById('existing_file').innerHTML = "No file uploaded";
            }

            $('#edit_modal').modal('show');
        }

        function normalizeFundSource(value) {
            const normalizedValue = String(value || '').trim();
            const fundSourceMap = {
                'CONTINGENCY FUND': 'Contingency Fund'
            };

            return fundSourceMap[normalizedValue.toUpperCase()] || normalizedValue;
        }
    </script>













    <!-- for print -->
    <script>
        function printCustom(rowData) {
            // Safe values
            let stud_no = rowData.stud_no || "";
            let req = rowData.req || "";
            let dat = rowData.dat || "";
            let inputBy = rowData.inputBy || "";
            let subjectText = rowData.subject || "";

            // Format date (YYYY-MM-DD -> Month DD, YYYY)
            let formattedDate = dat;
            if (dat) {
                let d = new Date(dat);
                if (!isNaN(d)) {
                    formattedDate = d.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }

            // Break subject into lines every 120 words
            let words = subjectText.split(" ");
            let result = "";
            words.forEach((word, i) => {
                result += word + " ";
                if ((i + 1) % 120 === 0) {
                    result += "<br>";
                }
            });

            // Build print content
            let content = `
        <p style="margin-left: 324;margin-top:100;"> ${stud_no}</p>
        <p style="margin-top: 25;margin-left:10;">${req}</p>
        <p style="margin-top: 25;margin-left:10;"> ${formattedDate}</p>
        <p style="position:absolute; margin-top: -13; margin-left:60; font-size:small;"> ${result}</p>
        <p style="position:absolute; top:173px; left:272px;">${inputBy}</p>
    `;

            // Open print window
            var printWindow = window.open('', '', 'height=600,width=1000');
            printWindow.document.write('<html><head><title>Print Document</title>');
            printWindow.document.write(`
        <style>
            body {
                margin: 0;
                font-family: Arial, sans-serif;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-area {
                width: 100%;
                min-height: 99vh;
                padding: 1px;
                background-image: url('/admin/print/bg-image.jpg');
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;
            }
        </style>
    `);
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div class="print-area">' + content + '</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    </script>






    <!-- Confirmation Modal -->
    <div class="modal fade" id="modal_confirm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">System</h3>
                </div>
                <div class="modal-body text-center">
                    <h4 class="text-danger">All files will be deleted.</h4>
                    <h3 class="text-danger">Are you sure you want to delete this data?</h3>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-times"></i> NO
                    </button>
                    <button type="button" class="btn btn-success" id="btn_yes">
                        <i class="glyphicon glyphicon-saved"></i> YES
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Bootstrap 4 Toast -->
    <!-- <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <div id="deleteToast" class="toast bg-success text-white" role="alert" data-delay="3000" style="min-width: 250px;">
            <div class="toast-body">
                ✅ Record deleted successfully!
            </div>
        </div>
    </div> -->


    <script>
        $(document).ready(function() {
            // When Delete button clicked
            $(document).on('click', '.btn-delete', function() {
                var stud_id2 = $(this).data('id');
                console.log("Deleting ID:", stud_id2);
                $("#modal_confirm").modal('show');
                $('#btn_yes').attr('data-id', stud_id2);
            });

            // When YES is clicked
            $('#btn_yes').on('click', function() {
                var id = $(this).attr('data-id');

                $.ajax({
                    type: "POST",
                    url: "delete_documents_funded.php",
                    data: {
                        stud_id2: id
                    },
                    success: function(response) {
                        console.log("Server response:", response);

                        // Hide the confirmation modal
                        $("#modal_confirm").modal('hide');

                        // ✅ Reload page after short delay
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                    }
                });
            });
        });
    </script>


































    <!-- Scripts -->
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#your-table').DataTable({
                ajax: {
                    url: 'fetch_funded_data.php',
                    type: 'GET',
                    dataSrc: ''
                },
                rowId: 'stud_id2',
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                columns: [{
                        title: 'DEO',
                        data: 'deo'
                    },
                    {
                        title: 'Project Code',
                        data: 'stud_no'
                    },
                    {
                        title: 'Project Name',
                        data: 'proj_name'
                    },
                    {
                        title: 'Municipality',
                        data: 'municipality'
                    },
                    {
                        title: 'Barangay',
                        data: 'barangay'
                    },
                    {
                        title: 'Purok/Sitio',
                        data: 'sitio'
                    },
                    {
                        title: 'FY',
                        data: 'cy'
                    },
                    {
                        title: 'Fund Source',
                        data: 'fund_source'
                    },
                    {
                        title: 'MOI',
                        data: 'moi'
                    },
                    {
                        title: 'Project Length<br>(Km/Unit)',
                        data: 'proj_target'
                    },
                    {
                        title: 'Project Scale<br>(As Program)',
                        data: 'proj_plan'
                    },
                    {
                        title: 'Appropriation',
                        data: 'cost',
                        render: function(data, type, row) {
                            if (data == null || data === '') return ''; // handle empty values
                            // Convert to number and format with PHP peso sign
                            let num = parseFloat(data);
                            if (isNaN(num)) return data; // return raw if not numeric
                            return '₱ ' + num.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        title: 'Proponent',
                        data: 'proponent'
                    },
                    {
                        title: 'Location Map',
                        data: 'file',
                        render: function(data, type, row, meta) {
                            return `<center><button class="btn btn-sm btn-info view-btn" data-file="${data}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-secondary files-btn" data-id="${row.stud_id2}" style="margin-top: 1px;">
                                        <i class="fas fa-folder-open"></i> Files
                                    </button></center>
                                `
                        }
                    },
                    {
                        title: 'ACTION',
                        data: 'file',
                        render: function(data, type, row, meta) {
                            return `
                                    <center><button style="margin-top: 1px;" class="btn btn-warning btn-sm edit-btn" data-row="${meta.row}">
                                    <i class="fas fa-edit"></i> Edit
                                    </button> <br>
                                    <button style="margin-top: 1px;" class="btn btn-danger btn-sm btn-delete" 
                                    data-id="${row.stud_id2}">
                                    <i class="fas fa-trash"></i> Del
                                    </button></center>
                                `;
                        }
                    }
                ],
                initComplete: function() {
                    const deoColumn = this.api().column(0);
                    const uniqueDEOs = [];

                    deoColumn.data().each(function(value) {
                        if (!uniqueDEOs.includes(value)) {
                            uniqueDEOs.push(value);
                        }
                    });

                    const deoFilter = $('#deo-filter');
                    uniqueDEOs.sort().forEach(deo => {
                        deoFilter.append(`<option value="${deo}">${deo}</option>`);
                    });

                    deoFilter.on('change', function() {
                        const selectedDEO = $(this).val();
                        deoColumn.search(selectedDEO).draw();
                    });
                }
            });

            function showFilesAlert(type, message) {
                $('#filesAlert')
                    .removeClass('alert-success alert-danger')
                    .addClass(type === 'success' ? 'alert-success' : 'alert-danger')
                    .text(message)
                    .show();
            }

            function showCutFilesAlert() {
                const cutItem = cutFile || cutFolder;

                if (!cutItem) {
                    return;
                }

                $('#filesAlert')
                    .removeClass('alert-success alert-danger')
                    .addClass('alert-success')
                    .html('"' + escapeHtml(cutItem.name) + '" cut. Choose a folder and click paste. <button type="button" class="btn btn-sm btn-outline-secondary ml-2" id="cancelCutFileBtn">Cancel</button>')
                    .show();
            }

            function resetFilesUploadProgress() {
                $('#filesUploadProgressContainer').hide();
                $('#filesUploadProgressBar').css('width', '0%');
                $('#filesUploadPercentText').text('0%');
                $('#filesUploadStats').text('0 MB / 0 MB');
                $('#filesUploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
            }

            function finishFilesUploadProgress() {
                $('#filesUploadProgressBar').css('width', '100%');
                $('#filesUploadPercentText').text('Upload complete');

                setTimeout(function() {
                    $('#filesUploadProgressContainer').fadeOut(200, function() {
                        $('#filesUploadProgressBar').css('width', '0%');
                        $('#filesUploadPercentText').text('0%');
                        $('#filesUploadStats').text('0 MB / 0 MB');
                    });
                }, 1000);
            }

            function escapeHtml(value) {
                return $('<div>').text(value || '').html();
            }

            let fundedFilesState = {
                folders: [],
                files: []
            };
            let collapsedFolderIds = {};
            let cutFile = null;
            let cutFolder = null;

            function folderLabel(folderId) {
                if (!folderId) {
                    return 'Project root';
                }

                const folder = fundedFilesState.folders.find(item => String(item.id) === String(folderId));
                return folder ? folder.name : 'Project root';
            }

            function updateFilesUploadTarget() {
                const folderId = $('#filesFolderId').val();
                $('#filesUploadTarget').text(folderLabel(folderId));
            }

            function hasActiveCut() {
                return !!(cutFile || cutFolder);
            }

            function clearActiveCut() {
                cutFile = null;
                cutFolder = null;
            }

            function updateCutActionState() {
                const isCutting = hasActiveCut();
                $('#filesNewFolderBtn').prop('disabled', isCutting);
            }

            function isFolderDescendant(folderId, possibleParentId) {
                let current = fundedFilesState.folders.find(folder => String(folder.id) === String(folderId));

                while (current && current.parent_id) {
                    if (String(current.parent_id) === String(possibleParentId)) {
                        return true;
                    }

                    current = fundedFilesState.folders.find(folder => String(folder.id) === String(current.parent_id));
                }

                return false;
            }

            function canPasteCutFolder(destinationFolderId) {
                if (!cutFolder) {
                    return false;
                }

                const destination = String(destinationFolderId || '');
                const currentParent = String(cutFolder.parent_id || '');

                return destination !== String(cutFolder.id) &&
                    destination !== currentParent &&
                    !isFolderDescendant(destination, cutFolder.id);
            }

            function renderFileRow(file, depth = 0) {
                const displayName = escapeHtml(file.original_name || file.file_name);
                const uploadedAt = escapeHtml(file.uploaded_at || '');
                const fileSize = escapeHtml(file.size_label || '0 B');
                const fileUrl = escapeHtml(file.url || '#');
                const isCut = cutFile && String(cutFile.id) === String(file.id);
                const cutDisabled = hasActiveCut() && !isCut ? 'disabled' : '';
                const disabledForCut = hasActiveCut() ? 'disabled' : '';

                return `
                    <div class="files-tree-row is-file ${isCut ? 'is-cut' : ''}" style="--tree-depth:${depth};">
                        <i class="far fa-file-alt text-muted"></i>
                        <div class="files-tree-main">
                            <span class="files-tree-name">${displayName}</span>
                            <span class="files-tree-meta">${isCut ? 'Ready to paste' : uploadedAt + ' - ' + fileSize}</span>
                        </div>
                        <div class="files-tree-actions">
                            <button type="button" class="btn btn-sm btn-light cut-file-btn" data-id="${file.id}" data-file-name="${displayName}" title="${isCut ? 'Cancel Cut' : 'Cut'}" ${cutDisabled}>
                                <i class="fas fa-cut"></i>
                            </button>
                            <a class="btn btn-sm btn-info ${hasActiveCut() ? 'disabled' : ''}" href="${hasActiveCut() ? '#' : fileUrl}" target="_blank" title="View" ${hasActiveCut() ? 'aria-disabled="true" tabindex="-1"' : ''}>
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-file-btn" data-id="${file.id}" title="Delete" ${disabledForCut}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }

            function renderFolderRow(folder, depth = 0) {
                const folderId = String(folder.id);
                const isCollapsed = !!collapsedFolderIds[folderId];
                const isSelected = String($('#filesFolderId').val()) === folderId;
                const icon = isCollapsed ? 'fa-chevron-right' : 'fa-chevron-down';
                const folderIcon = isCollapsed ? 'fa-folder' : 'fa-folder-open';
                const folderName = escapeHtml(folder.name);
                const childFolders = fundedFilesState.folders.filter(item => String(item.parent_id || '') === folderId);
                const childFiles = fundedFilesState.files.filter(file => String(file.folder_id || '') === folderId);
                const isCut = cutFolder && String(cutFolder.id) === folderId;
                const isProtected = folder.is_protected === true || folder.is_protected === 1 || folder.is_protected === '1';
                const canPaste = (cutFile && String(cutFile.folder_id || '') !== folderId) || canPasteCutFolder(folderId);
                const folderCutDisabled = (isProtected || (hasActiveCut() && !isCut)) ? 'disabled' : '';
                const folderCutTitle = isProtected ? 'Default folder cannot be cut' : (isCut ? 'Cancel Cut' : 'Cut Folder');
                const disabledForCut = hasActiveCut() ? 'disabled' : '';
                const editDisabled = (hasActiveCut() || isProtected) ? 'disabled' : '';
                const editTitle = isProtected ? 'Default folder cannot be renamed' : 'Edit Folder Name';
                const deleteDisabled = (hasActiveCut() || isProtected) ? 'disabled' : '';
                const deleteTitle = isProtected ? 'Default folder cannot be deleted' : 'Delete Folder';
                const childRows = isCollapsed ? '' : [
                    ...childFolders.map(childFolder => renderFolderRow(childFolder, depth + 1)),
                    ...childFiles.map(file => renderFileRow(file, depth + 1))
                ].join('');

                return `
                    <div class="files-tree-row is-folder files-folder-row ${isSelected ? 'is-selected' : ''} ${isCut ? 'is-cut' : ''}" data-folder-id="${folderId}" style="--tree-depth:${depth};">
                        <button type="button" class="files-icon-btn toggle-folder-btn" data-folder-id="${folderId}" title="Toggle Folder">
                            <i class="fas ${icon}"></i>
                        </button>
                        <i class="fas ${folderIcon} text-warning"></i>
                        <div class="files-tree-main">
                            <span class="files-tree-name">${folderName}</span>
                            <span class="files-tree-meta">${isCut ? 'Ready to paste' : childFolders.length + ' folder(s), ' + childFiles.length + ' file(s)'}</span>
                        </div>
                        <div class="files-tree-actions">
                            ${canPaste ? `
                                <button type="button" class="btn btn-sm btn-success paste-item-btn" data-folder-id="${folderId}" title="Paste Here">
                                    <i class="fas fa-paste"></i>
                                </button>
                            ` : ''}
                            <button type="button" class="btn btn-sm btn-light cut-folder-btn" data-folder-id="${folderId}" data-folder-name="${folderName}" title="${folderCutTitle}" ${folderCutDisabled}>
                                <i class="fas fa-cut"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light add-child-folder-btn" data-folder-id="${folderId}" title="New Folder Inside" ${disabledForCut}>
                                <i class="fas fa-folder-plus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light edit-folder-btn" data-folder-id="${folderId}" data-folder-name="${folderName}" title="${editTitle}" ${editDisabled}>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-folder-btn" data-folder-id="${folderId}" data-folder-name="${folderName}" title="${deleteTitle}" ${deleteDisabled}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${childRows}
                `;
            }

            function renderFundedFilesExplorer(response) {
                fundedFilesState.folders = response.folders || [];
                fundedFilesState.files = response.files || [];

                if (cutFile && !fundedFilesState.files.some(file => String(file.id) === String(cutFile.id))) {
                    clearActiveCut();
                    $('#filesAlert').hide();
                }

                if (cutFolder && !fundedFilesState.folders.some(folder => String(folder.id) === String(cutFolder.id))) {
                    clearActiveCut();
                    $('#filesAlert').hide();
                }

                const selectedFolderId = $('#filesFolderId').val();
                const folderExists = fundedFilesState.folders.some(folder => String(folder.id) === String(selectedFolderId));
                if (selectedFolderId && !folderExists) {
                    $('#filesFolderId').val('');
                }

                updateFilesUploadTarget();

                const rootFiles = fundedFilesState.files.filter(file => !file.folder_id || String(file.folder_id) === '0');
                const rootFolders = fundedFilesState.folders.filter(folder => !folder.parent_id || String(folder.parent_id) === '0');
                const rootSelected = !$('#filesFolderId').val();
                const canPasteRoot = (cutFile && cutFile.folder_id && String(cutFile.folder_id) !== '0') || canPasteCutFolder('');
                let html = `
                    <div class="files-tree-row files-root-row ${rootSelected ? 'is-selected' : ''}" data-folder-id="">
                        <i class="fas fa-home text-secondary"></i>
                        <div class="files-tree-main">
                            <span class="files-tree-name">Project root</span>
                            <span class="files-tree-meta">${rootFolders.length} folder(s), ${rootFiles.length} file(s)</span>
                        </div>
                        <div class="files-tree-actions">
                            ${canPasteRoot ? `
                                <button type="button" class="btn btn-sm btn-success paste-item-btn" data-folder-id="" title="Paste Here">
                                    <i class="fas fa-paste"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                    ${rootFolders.map(folder => renderFolderRow(folder, 0)).join('')}
                    ${rootFiles.map(file => renderFileRow(file, 0)).join('')}
                `;

                if (!fundedFilesState.folders.length && !fundedFilesState.files.length) {
                    html += '<div class="files-empty-state">No files attached yet.</div>';
                }

                $('#fundedFilesExplorer').html(html);
                updateCutActionState();
            }

            function renderFundedFilesTable(files) {
                if ($.fn.DataTable.isDataTable('#fundedFilesTable')) {
                    $('#fundedFilesTable').DataTable().clear().destroy();
                }

                $('#fundedFilesTable tbody').empty();

                $('#fundedFilesTable').DataTable({
                    data: files,
                    pageLength: 5,
                    lengthMenu: [
                        [5, 10, 25, 50, -1],
                        [5, 10, 25, 50, 'All']
                    ],
                    order: [
                        [1, 'desc']
                    ],
                    columns: [{
                            data: null,
                            render: function(data, type, row) {
                                const displayName = escapeHtml(row.original_name || row.file_name);
                                return `<span class="font-weight-bold">📄 ${displayName}</span>`;
                            }
                        },
                        {
                            data: 'uploaded_at',
                            defaultContent: ''
                        },
                        {
                            data: 'size_label',
                            defaultContent: '0 B'
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="text-nowrap">
                                        <center><a class="btn btn-sm btn-info" href="${row.url}" target="_blank">
                                            <i class="fas fa-eye"></i> View
                                        </a>

                                        <button type="button" class="btn btn-sm btn-danger delete-file-btn" data-id="${row.id}">
                                            <i class="fas fa-trash"></i>
                                        </button></center>
                                    </div>
                                `;
                            }
                        }
                    ],
                    language: {
                        emptyTable: 'No files attached yet.'
                    }
                });
            }


            // <a class="btn btn-sm btn-success" href="${row.url}" download>
            //                                 <i class="fas fa-download"></i>
            //                             </a>

            function loadFundedFiles(fundedId) {
                $('#fundedFilesExplorer').html('<div class="files-empty-state">Loading files...</div>');

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'list',
                        funded_id: fundedId
                    },
                    success: function(response) {
                        if (response.status !== 'success') {
                            showFilesAlert('error', response.message || 'Unable to load files.');
                            return;
                        }

                        renderFundedFilesExplorer(response);
                        return;

                        if (!response.files.length) {
                            list.html('<div class="text-muted text-center p-3">No files attached yet.</div>');
                            return;
                        }

                        const html = response.files.map(file => {
                            const displayName = file.original_name || file.file_name;
                            const uploadedAt = file.uploaded_at || '';
                            const fileSize = file.size_label || '0 B';

                            return `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div style="min-width:0;">
                                        <div class="font-weight-bold text-truncate">📄 ${displayName}</div>
                                        <small class="text-muted">${uploadedAt} &bull; ${fileSize}</small>
                                    </div>
                                    <div class="ml-2 text-nowrap">
                                        <a class="btn btn-sm btn-info" href="${file.url}" target="_blank">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a class="btn btn-sm btn-success" href="${file.url}" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-file-btn" data-id="${file.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }).join('');

                        list.html(html);
                    },
                    error: function(xhr) {
                        const message = xhr.responseText ? xhr.responseText.replace(/<[^>]*>/g, '').trim() : 'Unable to load files.';
                        $('#fundedFilesExplorer').html(`<div class="files-empty-state text-danger">${escapeHtml(message || 'Unable to load files.')}</div>`);
                    }
                });
            }

            $('#your-table').on('click', '.files-btn', function() {
                const fundedId = $(this).data('id');

                $('#filesFundedId').val(fundedId);
                $('#filesFolderId').val('');
                $('#fundedFilesInput').val('');
                $('#fundedFoldersInput').val('');
                $('#filesAlert').hide();
                clearActiveCut();
                collapsedFolderIds = {};
                updateFilesUploadTarget();
                resetFilesUploadProgress();
                $('#filesModal').modal('show');
                loadFundedFiles(fundedId);
            });

            $('#filesNewFileBtn').on('click', function() {
                $('#fundedFilesInput').trigger('click');
            });

            $('#filesChooseFolderBtn').on('click', function() {
                $('#fundedFoldersInput').trigger('click');
            });

            function createProjectFolder(parentId) {
                const fundedId = $('#filesFundedId').val();
                const folderName = prompt('Folder name');

                if (folderName === null) {
                    return;
                }

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'create_folder',
                        funded_id: fundedId,
                        parent_id: parentId,
                        folder_name: folderName
                    },
                    success: function(response) {
                        showFilesAlert(response.status === 'success' ? 'success' : 'error', response.message || 'Done.');
                        if (response.status === 'success') {
                            $('#filesFolderId').val(parentId || '');
                            updateFilesUploadTarget();
                            loadFundedFiles(fundedId);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseText ? xhr.responseText.replace(/<[^>]*>/g, '').trim() : 'Folder could not be created.';
                        showFilesAlert('error', message || 'Folder could not be created.');
                    }
                });
            }

            $('#filesNewFolderBtn').on('click', function() {
                if (hasActiveCut()) {
                    return;
                }

                createProjectFolder($('#filesFolderId').val());
            });

            $('#fundedFilesExplorer').on('click', '.add-child-folder-btn', function(e) {
                e.stopPropagation();
                if (hasActiveCut()) {
                    return;
                }

                const parentId = $(this).data('folder-id') || '';
                collapsedFolderIds[String(parentId)] = false;
                createProjectFolder(parentId);
            });

            $('#filesRefreshBtn').on('click', function() {
                const fundedId = $('#filesFundedId').val();
                if (fundedId) {
                    loadFundedFiles(fundedId);
                }
            });

            $('#filesCollapseBtn').on('click', function() {
                collapsedFolderIds = {};
                fundedFilesState.folders.forEach(folder => {
                    collapsedFolderIds[String(folder.id)] = true;
                });
                renderFundedFilesExplorer(fundedFilesState);
            });

            $('#fundedFilesExplorer').on('click', '.files-folder-row, .files-root-row', function(e) {
                if ($(e.target).closest('button, a').length) {
                    return;
                }

                $('#filesFolderId').val($(this).data('folder-id') || '');
                updateFilesUploadTarget();
                renderFundedFilesExplorer(fundedFilesState);
            });

            $('#fundedFilesExplorer').on('click', '.toggle-folder-btn', function(e) {
                e.stopPropagation();
                const folderId = String($(this).data('folder-id'));
                collapsedFolderIds[folderId] = !collapsedFolderIds[folderId];
                renderFundedFilesExplorer(fundedFilesState);
            });

            $('#fundedFilesExplorer').on('click', '.edit-folder-btn', function(e) {
                e.stopPropagation();

                const fundedId = $('#filesFundedId').val();
                const folderId = $(this).data('folder-id');
                const currentName = $(this).data('folder-name');
                const folderName = prompt('Edit folder name', currentName);

                if (folderName === null) {
                    return;
                }

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'rename_folder',
                        funded_id: fundedId,
                        folder_id: folderId,
                        folder_name: folderName
                    },
                    success: function(response) {
                        showFilesAlert(response.status === 'success' ? 'success' : 'error', response.message || 'Done.');
                        if (response.status === 'success') {
                            loadFundedFiles(fundedId);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseText ? xhr.responseText.replace(/<[^>]*>/g, '').trim() : 'Folder could not be renamed.';
                        showFilesAlert('error', message || 'Folder could not be renamed.');
                    }
                });
            });

            $('#fundedFilesExplorer').on('click', '.delete-folder-btn', function(e) {
                e.stopPropagation();

                if (hasActiveCut()) {
                    showCutFilesAlert();
                    return;
                }

                const fundedId = $('#filesFundedId').val();
                const folderId = $(this).data('folder-id');
                const folderName = $(this).data('folder-name') || 'this folder';

                if (!confirm('Delete "' + folderName + '" and all files inside it?')) {
                    return;
                }

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'delete_folder',
                        funded_id: fundedId,
                        folder_id: folderId
                    },
                    success: function(response) {
                        showFilesAlert(response.status === 'success' ? 'success' : 'error', response.message || 'Done.');

                        if (response.status === 'success') {
                            if (String($('#filesFolderId').val()) === String(folderId)) {
                                $('#filesFolderId').val('');
                                updateFilesUploadTarget();
                            }

                            delete collapsedFolderIds[String(folderId)];
                            loadFundedFiles(fundedId);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseText ? xhr.responseText.replace(/<[^>]*>/g, '').trim() : 'Folder delete failed.';
                        showFilesAlert('error', message || 'Folder delete failed.');
                    }
                });
            });

            $('#fundedFilesExplorer').on('click', '.cut-file-btn', function(e) {
                e.stopPropagation();

                const attachmentId = $(this).data('id');

                if (cutFile && String(cutFile.id) === String(attachmentId)) {
                    clearActiveCut();
                    $('#filesAlert').hide();
                    renderFundedFilesExplorer(fundedFilesState);
                    return;
                }

                const file = fundedFilesState.files.find(item => String(item.id) === String(attachmentId));

                if (!file) {
                    showFilesAlert('error', 'File was not found.');
                    return;
                }

                cutFolder = null;
                cutFile = {
                    id: file.id,
                    name: file.original_name || file.file_name,
                    folder_id: file.folder_id || ''
                };

                showCutFilesAlert();
                renderFundedFilesExplorer(fundedFilesState);
            });

            $('#filesAlert').on('click', '#cancelCutFileBtn', function() {
                clearActiveCut();
                $('#filesAlert').hide();
                renderFundedFilesExplorer(fundedFilesState);
            });

            $('#fundedFilesExplorer').on('click', '.cut-folder-btn', function(e) {
                e.stopPropagation();

                const folderId = $(this).data('folder-id');

                if (cutFolder && String(cutFolder.id) === String(folderId)) {
                    clearActiveCut();
                    $('#filesAlert').hide();
                    renderFundedFilesExplorer(fundedFilesState);
                    return;
                }

                const folder = fundedFilesState.folders.find(item => String(item.id) === String(folderId));

                if (!folder) {
                    showFilesAlert('error', 'Folder was not found.');
                    return;
                }

                const isProtected = folder.is_protected === true || folder.is_protected === 1 || folder.is_protected === '1';
                if (isProtected) {
                    showFilesAlert('error', 'Default project folders cannot be cut.');
                    return;
                }

                cutFile = null;
                cutFolder = {
                    id: folder.id,
                    name: folder.name,
                    parent_id: folder.parent_id || ''
                };

                showCutFilesAlert();
                renderFundedFilesExplorer(fundedFilesState);
            });

            $('#fundedFilesExplorer').on('click', '.paste-item-btn', function(e) {
                e.stopPropagation();

                const fundedId = $('#filesFundedId').val();
                const folderId = $(this).data('folder-id') || '';

                if (!hasActiveCut()) {
                    showFilesAlert('error', 'Please cut a file or folder first.');
                    return;
                }

                const requestData = cutFile ? {
                    action: 'move_file',
                    funded_id: fundedId,
                    id: cutFile.id,
                    folder_id: folderId
                } : {
                    action: 'move_folder',
                    funded_id: fundedId,
                    folder_id: cutFolder.id,
                    parent_id: folderId
                };

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'POST',
                    dataType: 'json',
                    data: requestData,
                    success: function(response) {
                        showFilesAlert(response.status === 'success' ? 'success' : 'error', response.message || 'Done.');

                        if (response.status === 'success') {
                            clearActiveCut();
                            $('#filesFolderId').val(folderId);
                            updateFilesUploadTarget();
                            loadFundedFiles(fundedId);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseText ? xhr.responseText.replace(/<[^>]*>/g, '').trim() : 'Move failed.';
                        showFilesAlert('error', message || 'Move failed.');
                    }
                });
            });

            function addUploadFile(formData, file, relativePath) {
                const path = (relativePath || file.webkitRelativePath || '').replace(/\\/g, '/');
                formData.append('files[]', file, path || file.name);
                formData.append('relative_paths[]', path);
            }

            function collectDirectoryEntry(entry, basePath) {
                return new Promise(resolve => {
                    if (!entry) {
                        resolve([]);
                        return;
                    }

                    if (entry.isFile) {
                        entry.file(file => {
                            resolve([{
                                file,
                                path: (basePath ? basePath + '/' : '') + file.name
                            }]);
                        }, () => resolve([]));
                        return;
                    }

                    if (!entry.isDirectory) {
                        resolve([]);
                        return;
                    }

                    const reader = entry.createReader();
                    const entries = [];

                    function readBatch() {
                        reader.readEntries(batch => {
                            if (!batch.length) {
                                Promise.all(entries.map(child => collectDirectoryEntry(child, (basePath ? basePath + '/' : '') + entry.name)))
                                    .then(groups => resolve([].concat(...groups)));
                                return;
                            }

                            entries.push(...batch);
                            readBatch();
                        }, () => resolve([]));
                    }

                    readBatch();
                });
            }

            function collectDroppedUploadFiles(dataTransfer) {
                const items = Array.from(dataTransfer && dataTransfer.items ? dataTransfer.items : []);

                if (!items.length) {
                    return Promise.resolve(Array.from(dataTransfer && dataTransfer.files ? dataTransfer.files : []).map(file => ({
                        file,
                        path: file.webkitRelativePath || file.name
                    })));
                }

                const tasks = items.map(item => {
                    const entry = item.webkitGetAsEntry ? item.webkitGetAsEntry() : null;
                    if (entry) {
                        return collectDirectoryEntry(entry, '');
                    }

                    const file = item.getAsFile ? item.getAsFile() : null;
                    return Promise.resolve(file ? [{
                        file,
                        path: file.name
                    }] : []);
                });

                return Promise.all(tasks).then(groups => [].concat(...groups));
            }

            function uploadDroppedFiles(uploadItems, folderId) {
                const files = Array.from(uploadItems || []).filter(item => item && item.file);
                const fundedId = $('#filesFundedId').val();

                if (!fundedId || !files.length) {
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'upload');
                formData.append('funded_id', fundedId);
                formData.append('folder_id', folderId || '');

                files.forEach(item => {
                    addUploadFile(formData, item.file, item.path);
                });

                $('#filesFolderId').val(folderId || '');
                updateFilesUploadTarget();
                $('#filesAlert').hide();

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#filesUploadProgressContainer').show();
                        $('#filesUploadProgressBar').css('width', '0%');
                        $('#filesUploadPercentText').text('0%');
                        $('#filesUploadStats').text('0 MB / 0 MB');
                        $('#filesUploadBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
                    },
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener('progress', function(e) {
                            if (!e.lengthComputable) return;

                            const percent = Math.round((e.loaded / e.total) * 100);
                            const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                            const totalMB = (e.total / (1024 * 1024)).toFixed(2);

                            $('#filesUploadProgressBar').css('width', percent + '%');
                            $('#filesUploadPercentText').text(percent >= 100 ? 'Processing...' : percent + '%');
                            $('#filesUploadStats').text(`${loadedMB} MB / ${totalMB} MB`);
                        });

                        return xhr;
                    },
                    success: function(response) {
                        showFilesAlert(response.status === 'success' ? 'success' : 'error', response.message || 'Upload finished.');

                        if (response.status === 'success') {
                            finishFilesUploadProgress();
                            loadFundedFiles(fundedId);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseText ? xhr.responseText.replace(/<[^>]*>/g, '').trim() : 'Upload failed.';
                        showFilesAlert('error', message || 'Upload failed.');
                    },
                    complete: function() {
                        $('#filesUploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
                    }
                });
            }

            $('#fundedFilesExplorer').on('dragenter dragover', '.files-root-row, .files-folder-row', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('.files-tree-row, #fundedFilesExplorer').removeClass('is-drag-over');
                $(this).addClass('is-drag-over');
            });

            $('#fundedFilesExplorer').on('dragenter dragover', function(e) {
                e.preventDefault();

                if ($(e.target).closest('.files-root-row, .files-folder-row').length) {
                    return;
                }

                $('.files-tree-row').removeClass('is-drag-over');
                $(this).addClass('is-drag-over');
            });

            $('#fundedFilesExplorer').on('dragleave', '.files-root-row, .files-folder-row', function(e) {
                e.preventDefault();
                $(this).removeClass('is-drag-over');
            });

            $('#fundedFilesExplorer').on('dragleave', function(e) {
                if (!this.contains(e.relatedTarget)) {
                    $(this).removeClass('is-drag-over');
                    $('.files-tree-row').removeClass('is-drag-over');
                }
            });

            $('#fundedFilesExplorer').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const dropTarget = $(e.target).closest('.files-root-row, .files-folder-row');
                const folderId = dropTarget.length ? (dropTarget.data('folder-id') || '') : '';
                $('.files-tree-row, #fundedFilesExplorer').removeClass('is-drag-over');
                collectDroppedUploadFiles(e.originalEvent.dataTransfer).then(files => {
                    uploadDroppedFiles(files, folderId);
                });
            });

            $('#fundedFilesForm').on('submit', function(e) {
                e.preventDefault();

                const fundedId = $('#filesFundedId').val();
                const fileInput = $('#fundedFilesInput')[0];
                const folderInput = $('#fundedFoldersInput')[0];
                const hasFiles = (fileInput.files && fileInput.files.length > 0) || (folderInput.files && folderInput.files.length > 0);

                if (!hasFiles) {
                    showFilesAlert('error', 'Please choose at least one file.');
                    return;
                }

                const formData = new FormData(this);

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#filesAlert').hide();
                        $('#filesUploadProgressContainer').show();
                        $('#filesUploadProgressBar').css('width', '0%');
                        $('#filesUploadPercentText').text('0%');
                        $('#filesUploadStats').text('0 MB / 0 MB');
                        $('#filesUploadBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
                    },
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener('progress', function(e) {
                            if (!e.lengthComputable) return;

                            const percent = Math.round((e.loaded / e.total) * 100);
                            const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                            const totalMB = (e.total / (1024 * 1024)).toFixed(2);

                            $('#filesUploadProgressBar').css('width', percent + '%');
                            $('#filesUploadPercentText').text(percent >= 100 ? 'Processing...' : percent + '%');
                            $('#filesUploadStats').text(`${loadedMB} MB / ${totalMB} MB`);
                        });

                        return xhr;
                    },
                    success: function(response) {
                        showFilesAlert(response.status === 'success' ? 'success' : 'error', response.message || 'Upload finished.');

                        if (response.status === 'success') {
                            $('#fundedFilesInput').val('');
                            $('#fundedFoldersInput').val('');
                            finishFilesUploadProgress();
                            loadFundedFiles(fundedId);
                        }
                    },
                    error: function() {
                        showFilesAlert('error', 'Upload failed.');
                    },
                    complete: function() {
                        $('#filesUploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
                    }
                });
            });

            $('#fundedFilesExplorer').on('click', '.delete-file-btn', function() {
                if (hasActiveCut()) {
                    showCutFilesAlert();
                    return;
                }

                if (!confirm('Delete this file?')) {
                    return;
                }

                const attachmentId = $(this).data('id');
                const fundedId = $('#filesFundedId').val();

                $.ajax({
                    url: 'funded_attachments.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'delete',
                        id: attachmentId
                    },
                    success: function(response) {
                        if (cutFile && String(cutFile.id) === String(attachmentId)) {
                            clearActiveCut();
                        }
                        showFilesAlert(response.status === 'success' ? 'success' : 'error', response.message || 'Done.');
                        loadFundedFiles(fundedId);
                    },
                    error: function() {
                        showFilesAlert('error', 'Delete failed.');
                    }
                });
            });

            // Handle View button click
            // $('#your-table').on('click', '.view-btn', function () {
            //     const file = $(this).data('file');
            //     $('#docFrame').attr('src', '/admin/uploads/' + encodeURIComponent(file));
            //     $('#viewModal').modal('show');
            // });


            // ✅ REL dropdown filter
            $('#rel-filter').on('change', function() {
                table.column(7).search(this.value).draw(); // column 7 = REL
            });


            //  Handle View button click
            // $('#your-table').on('click', '.view-btn', function() {
            //     const file = $(this).data('file');

            //     if (file && file.trim() !== '') {
            //         $('#docFrame').attr('src', '/admin/funded_uploads/' + encodeURIComponent(file)).show();
            //         $('#docMessage').hide();
            //     } else {
            //         $('#docFrame').hide().attr('src', ''); // clear iframe
            //         $('#docMessage').show();
            //     }

            //     $('#viewModal').modal('show');
            // });

            let zoomLevel = 1;

            $('#your-table').on('click', '.view-btn', function() {
                const file = $(this).data('file');
                const iframe = $('#docFrame');
                const message = $('#docMessage');

                iframe.hide();
                message.hide();
                iframe.removeAttr('src');
                iframe.removeAttr('srcdoc');

                if (!file || file.trim() === '') {
                    message.show();
                    $('#viewModal').modal('show');
                    return;
                }

                const ext = file.split('.').pop().toLowerCase();
                const filePath = 'funded_uploads/' + encodeURIComponent(file);

                // PDF → normal iframe
                if (ext === 'pdf') {
                    iframe.attr('src', filePath).show();
                }

                // IMAGE → wrapped in HTML (prevents auto zoom)
                else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    zoomLevel = 1;

                    const html = `
        <!DOCTYPE html>
        <html>
        <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
        html, body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #f5f5f5;
        }

       #toolbar {
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 999;

    display: flex;
    align-items: center;
    justify-content: flex-end; /* 👈 force right alignment */
    gap: 6px;
}


#toolbar button {
    width: 36px;
    height: 36px;
    padding: 0;
    font-size: 18px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    background: #fff;
    border-radius: 6px;
}


#toolbar button:hover {
    background: #eaeaea;
}

#toolbar button:active {
    transform: scale(0.95);
}


        #container {
            width: 100%;
            height: 100%;
            overflow: auto;
            cursor: zoom-out;
        }

        #container {
            cursor: zoom-in;
        }

        img {
            display: block;
            margin: auto;
            max-width: 100%;
            max-height: 100%;
            transform-origin: 0 0;
            transition: transform 0.05s ease-out;
            user-select: none;
        }


            @media print {
        body {
            margin: 0;
            background: #fff;
        }

            #toolbar {
                display: none !important;
            }

            #container {
                overflow: visible;
            }
        
            img {
                max-width: 100%;
                max-height: 100%;
            }
        }

        </style>
        </head>
        <body>

        <div id="toolbar">
            <button id="zoomIn">➕</button>
            <button id="zoomOut">➖</button>
            <button id="resetZoom">🔁</button>
            <button id="fitScreen">⛶</button>
            <button id="saveImage">⬇️</button>
            <button id="printImage">⎙</button>
        </div>

        <div id="container">
            <img id="zoomImg" src="${filePath}">
        </div>

        </body>
        </html>
        `;

                    iframe.attr('srcdoc', html).show();
                }

                // Unsupported
                else {
                    message.text('❌ Preview not available.').show();
                }

                $('#viewModal').modal('show');
            });




            $('#docFrame').on('load', function() {
                const iframe = document.getElementById('docFrame');

                if (!iframe.contentDocument) return;

                const img = iframe.contentDocument.getElementById('zoomImg');
                const container = iframe.contentDocument.getElementById('container');
                const doc = iframe.contentDocument;

                if (!img || !container) return;

                let zoom = 1;

                // ⭐ Cursor icons
                const zoomInCursor = "url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\"><path d=\"M10 2a8 8 0 1 1 0 16 8 8 0 0 1 0-16zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12zm9.5 15.5L16 14l1.4-1.4 3.5 3.5-1.4 1.4z\" fill=\"%23000\"/><path d=\"M10 7v6H4v2h6v6h2v-6h6v-2h-6V7h-2z\" fill=\"%23000\"/></svg>') 12 12, auto;";

                const zoomOutCursor = "url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\"><path d=\"M10 2a8 8 0 1 1 0 16 8 8 0 0 1 0-16zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12zm9.5 15.5L16 14l1.4-1.4 3.5 3.5-1.4 1.4z\" fill=\"%23000\"/><path d=\"M6 11h8v2H6v-2z\" fill=\"%23000\"/></svg>') 12 12, auto;";

                function updateCursor() {
                    if (zoom === 1) container.style.cursor = zoomInCursor;
                    else container.style.cursor = zoomOutCursor;
                }

                // INITIAL CURSOR
                updateCursor();

                // ===== LEFT CLICK ZOOM =====
                container.addEventListener('click', function(e) {
                    if (e.button !== 0) return; // left click only

                    e.preventDefault();

                    const rect = img.getBoundingClientRect();
                    const clickX = e.clientX - rect.left + container.scrollLeft;
                    const clickY = e.clientY - rect.top + container.scrollTop;

                    // Toggle zoom in/out
                    if (zoom === 1) zoom = 2;
                    else zoom = 1;

                    img.style.transformOrigin = `${clickX}px ${clickY}px`;
                    img.style.transform = `scale(${zoom})`;

                    updateCursor();
                });

                // ===== BUTTONS =====
                doc.getElementById('zoomIn').onclick = () => {
                    zoom = Math.min(zoom + 0.2, 5);
                    img.style.transform = `scale(${zoom})`;
                    updateCursor();
                };

                doc.getElementById('zoomOut').onclick = () => {
                    zoom = Math.max(zoom - 0.2, 0.5);
                    img.style.transform = `scale(${zoom})`;
                    updateCursor();
                };

                doc.getElementById('resetZoom').onclick = () => {
                    zoom = 1;
                    img.style.transform = `scale(${zoom})`;
                    container.scrollTop = 0;
                    container.scrollLeft = 0;
                    updateCursor();
                };

                doc.getElementById('fitScreen').onclick = () => {
                    const cw = container.clientWidth;
                    const ch = container.clientHeight;

                    const iw = img.naturalWidth;
                    const ih = img.naturalHeight;

                    const scaleX = cw / iw;
                    const scaleY = ch / ih;

                    zoom = Math.min(scaleX, scaleY);
                    zoom = Math.min(Math.max(zoom, 0.5), 5);

                    img.style.transformOrigin = '0 0';
                    img.style.transform = `scale(${zoom})`;

                    container.scrollLeft = 0;
                    container.scrollTop = 0;
                    updateCursor();
                };

                doc.getElementById('saveImage').onclick = () => {
                    const link = doc.createElement('a');
                    link.href = img.src;
                    link.download = img.src.split('/').pop();
                    doc.body.appendChild(link);
                    link.click();
                    doc.body.removeChild(link);
                };




                doc.getElementById('printImage').onclick = () => {
                    const toolbar = doc.getElementById('toolbar');

                    // Save current state
                    const prevTransform = img.style.transform;
                    const prevOrigin = img.style.transformOrigin;
                    const prevScrollTop = container.scrollTop;
                    const prevScrollLeft = container.scrollLeft;
                    const prevToolbarDisplay = toolbar.style.display;

                    // Prepare image for printing
                    img.style.transformOrigin = 'center center';
                    img.style.transform = `scale(${zoom})`;

                    // Hide toolbar
                    toolbar.style.display = 'none';

                    // Print from iframe (no new tab, no blink)
                    doc.defaultView.focus();
                    doc.defaultView.print();

                    // Restore everything after print
                    setTimeout(() => {
                        img.style.transform = prevTransform;
                        img.style.transformOrigin = prevOrigin;
                        container.scrollTop = prevScrollTop;
                        container.scrollLeft = prevScrollLeft;
                        toolbar.style.display = prevToolbarDisplay;
                    }, 500);
                };


            });






            // Handle Edit button click
            $('#your-table').on('click', '.edit-btn', function() {
                const rowIndex = $(this).data('row');
                const rowData = $('#your-table').DataTable().row(rowIndex).data();
                openModal(rowData);
            });



            // Handle Print button click
            // $('#your-table').on('click', '.print-btn', function() {
            //     const rowIndex = $(this).closest('tr').index();
            //     const rowData = $('#your-table').DataTable().row(rowIndex).data();
            //     printCustom(rowData);
            // });

            // Handle Print button click
            $('#your-table').on('click', '.print-btn', function() {
                // Use DataTables API to get row data correctly
                const rowData = $('#your-table').DataTable().row($(this).closest('tr')).data();
                printCustom(rowData);
            });


            // If you’re using responsive tables or child rows, add a safeguard:
            // $('#your-table').on('click', '.print-btn', function() {
            //     const table = $('#your-table').DataTable();
            //     const tr = $(this).closest('tr');
            //     const row = table.row(tr.hasClass('child') ? tr.prev() : tr);
            //     const rowData = row.data();
            //     printCustom(rowData);
            // });
            // This handles cases where DataTables creates hidden “child rows” in responsive mode.






            $(document).on('click', '.btn-delete', function() {
                var stud_id2 = $(this).data('id'); // ✅ use data-id instead of id
                console.log("Deleting ID:", stud_id2);
                $("#modal_confirm").modal('show');
                $('#btn_yes').attr('name', stud_id2);
            });




            // Style ng header
            $('#your-table thead th').css({
                'background-color': '#0a376e',
                'color': '#ffffff',
                'text-align': 'center',
                'vertical-align': 'middle',
                'font-weight': 'normal'
            });




        });
    </script>
</body>

</html>

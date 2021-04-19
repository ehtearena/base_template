<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/admin/">Administration</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Tables</a></li>
    <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
</ol>
<div class="subheader">
    <h1 class="subheader-title">
        <i class='subheader-icon fal fa-pencil-ruler'></i> Administration<span class='fw-300'></span>
        <small>
          System maintenance.
        </small>
    </h1>
</div>
<div class="row">

<?php
$icons = array("users",
              "map-marker-alt",
              "map-marker-alt",
              "map-marker-alt",
              "map-marker-alt",
							);
$name = array("Users",
              "County",
              "Sub-county",
              "Constituency",
              "Ward",
							);
$description = array("Manage registrar system users.",
              "Maintain counties",
              "Maintain sub-counties",
              "Maintain constituencies",
              "Maintain wards",
							);
$links = array("/admin/userTable",
              "/admin/countyTable",
              "/admin/sub_countyTable",
              "/admin/constituencyTable",
              "/admin/wardTable",
						  );

$cnt = 0;
foreach ($icons as $icon)
{

	?>
<div class="col-lg-6">
																<div class="card mb-2">
	                                    <div class="card-body">
	                                        <a href="<?php echo $links[$cnt]; ?>" class="d-flex flex-row align-items-center">
	                                            <div class="icon-stack display-3 flex-shrink-0">
	                                                <i class="fal fa-circle icon-stack-3x opacity-100 color-primary-400"></i>
	                                                <i class="fal fa-<?php echo $icon ?> icon-stack-1x opacity-100 color-primary-500"></i>
	                                            </div>
	                                            <div class="ml-3">
	                                                <strong>
	                                                    <?php echo $name[$cnt]; ?>
	                                                </strong>
	                                                <br>
																									<?php echo $description[$cnt]; ?>
	                                            </div>
	                                        </a>
	                                    </div>
	                                </div>
</div>

<?php
$cnt++;
}
?>
</div>

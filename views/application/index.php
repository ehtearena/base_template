    <ol class="breadcrumb page-breadcrumb">
        <li class="breadcrumb-item"><a href="/">Home</a></li>
        <li class="breadcrumb-item"><a href="/search">Application</a></li>
        <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
    </ol>
    <div class="subheader">
        <h1 class="subheader-title">
            <i class='subheader-icon fal fa-search'></i> Application<span class='fw-300'></span>
            <small>
              Appications
            </small>
        </h1>
    </div>

<div class="row">
<div class="col-xl-12">

<div id="panel-1" class="panel" style="clear:both">
   <div class="panel-hdr">
      <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">Application</h2>
      <div class="panel-toolbar">
         <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
         <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
      </div>
   </div>
   <div class="panel-container show">
      <div class="panel-content">
         <div class="frame-wrap">
               <div class="row" style="border-bottom: 1px solid #CCC">
                     <div class="d-none d-md-block col-lg-1 col-md-1 col-sm-1 col-xs-1">#</div>
                     <div class="d-none d-md-block col-lg-1 col-md-1 col-sm-1 col-xs-1">Document #</div>
                     <div class="col-md-1">Last updated</div>
                     <div class="col-md-2">Full Name</div>
                     <div class="col-md-3">Email</div>
                     <div class="col-md-2">Search Names</div>
                     <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1">Status</div>
               </div>

<?php
$thisCnt = 0;
foreach ($view['disp'] as $vd)
{
$thisCnt++;
$gs = db_fetch("",1,"action_datetime_HD","desc","document_name = '".$view['table']."' AND document_ID = ".$vd['id'],"s.*, r.name as rate_sheet_name","document_status d LEFT JOIN search_status s ON s.step = d.status_ID LEFT JOIN rate_sheet r ON r.id = s.payment_step_ID", false, false, null);
$statusname = "Save Draft";
$status_color = "";
if ($gs[0]['name'] != null)
{
    if ($_SESSION['user_level'] == 1 && strpos(",".$gs[0]['message_audience'].",", ",1,") === false)
    {
      $statusname = "Pending";
    }
    else
    {
      $statusname = $gs[0]['name'];
    }

  $status_color = "";
  if (isset($gs[0]['status_color']) and $gs[0]['status_color'] != "")
  {
    $status_color = 'style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px solid #CCC; background-color:'.$gs[0]["status_color"].'";';
  }
  else {
    $status_color = 'style="padding-bottom: 20px; padding-top: 20px; border-bottom: 1px solid #CCC";';
  }
}


?>
                  <div class="row" <?php echo $status_color; ?> >
                     <div class="d-none d-md-block col-lg-1 col-md-1 col-sm-1 col-xs-1"><?php echo $thisCnt; ?></div>
                     <div class="d-none d-md-block col-lg-1 col-md-1 col-sm-1 col-xs-1"><?php echo $vd['id']; ?></div>
                     <div class="col-md-1 col-xs-1"><?php echo date('j M', strtotime($vd['updatedAt'])); ?></div>
                     <div class="col-md-2"><?php echo $vd['Full_Name']; ?></div>
                     <div class="col-md-3"><?php echo $vd['email']; ?></div>
                     <div class="col-md-2"><b><?php echo $vd['proposed_name_1']."<BR>".$vd['proposed_name_2']."<BR>".$vd['proposed_name_3']; ?></b></div>
                     <div class="col-lg-2 col-md-2 col-sm-2 col-xs-1"><?php echo $statusname; ?></div>
                     <div class="col-md-12" style="text-align:right;">
                       <?php echo "<a class='btn btn-primary' style='margin-top:5px;margin-left:5px;' href='/".$view['table']."/editRecord?id=".$vd['id']."'><i class='fal fa-edit'></i> View</a>"; ?>
                       <?php
                       if ($gs[0]['step'] >= 3)
                       {
                         echo "<a class='btn btn-info' style='margin-top:5px;margin-left:5px;' target='_blank' href='/document_templates/pdf_document?document_name=search&document_ID=".$vd['id']."&template=AS1'><i class='fal fa-file-pdf'></i> Application</a>";
                       }

                       if ($gs[0]['step'] >= 6)
                       {
                         echo "<a class='btn btn-success' style='margin-top:5px;margin-left:5px;' target='_blank' href='/document_templates/pdf_document?document_name=search&document_ID=".$vd['id']."&template=AS2'><i class='fal fa-file-pdf'></i> Certificate</a>";
                       }

                       if ($gs[0]['step'] >= 6 && $_SESSION['user_level'] == 1)
                       {
                         if ($vd['regid']== "") echo "<a class='btn btn-primary' style='margin-top:5px;margin-left:5px;' onclick=\"updatePopupLink(".$vd['id']."); jQuery('#registrationFeeModal').modal('show'); return false;\" href='#'><i class='fal fa-file-export'></i> Apply for Registration</a>";
                       }

                         ?>

                     </div>
                  </div>
<?php
}
?>
      </div>
   </div>
</div>
</div>
</div>
</div>

<nav aria-label="Page navigation">
   <ul class="pagination">
   <li class="page-item"><a class="page-link" href="/<?php echo $view['table']; ?>?page=<?php echo ($view['page'] <= 1 ? 1 : $view['page']-1); ?>">Previous</a></li>
   <?php
   if ($view['page'] > 1)
   {
     ?>
     <li class="page-item"><a class="page-link" href="/<?php echo $view['table']; ?>?page=<?php echo ($view['page']-1); ?>"><?php echo ($view['page']-1); ?></a></li>
     <?php
   }
   ?>
   <li class="page-item active" aria-current="page">
     <a class="page-link" href="#"><?php echo $view['page']; ?><span class="sr-only"></span></a>
   </li>
   <?php
   if ($view['page'] < $view['pages'])
   {
     ?>
     <li class="page-item"><a class="page-link" href="/<?php echo $view['table']; ?>?page=<?php echo ($view['page']+1); ?>"><?php echo ($view['page']+1); ?></a></li>
     <?php
   }
   ?>
   <li class="page-item"><a class="page-link" href="/<?php echo $view['table']; ?>?page=<?php echo ($view['page'] >= $view['pages'] ? $view['pages'] : $view['page']+1); ?>">Next</a></li>
   </ul>
 </nav>

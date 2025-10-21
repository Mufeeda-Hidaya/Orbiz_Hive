<?php include "common/header.php";?>
        <!-- partial -->
	
        <div class="content-wrapper right_container">
				<div class="form-control">
					<div class="row">
						<div class="col-md-6">
							<h3>Manage Users</h3>
						</div>
						<div class="col-md-6 text-right">
							<a href="<?= base_url('adduser') ?>"><button class="btn btn-secondary">Add New User</button></a>
					
						</div>
						<div class="col-md-12"><hr/></div>
					</div>
					<div class="col-md-6 no-gutters">{Listing table goes here}</div>
						<div class="ml-auto">
							<i class="mdi mdi-heart-outline text-muted"></i>
						</div>
				</div>
			</div>
		
<?php include "common/footer.php";?>
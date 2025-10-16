<input type="hidden" id="enquiry_id" value="<?= $enquiry->enquiry_id ?>">
<div class="container-fluid py-2">
  <div class="my-3"></div>
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-center">
            <h5 class="text-white mb-0">Enquiry Details</h5>
          </div>
        </div>
        <div id="messageBox" class="alert d-none text-center" role="alert"></div>
        <div class="card-body">
          <div class="row mb-4">
            <!-- Left: Customer Info -->
            <div class="col-md-6">
              <!-- <h6 class="fw-bold">Customer Info</h6> -->
              <p class="mb-1"><strong>Name:</strong> <?= $enquiry->name ?></p>
              <p class="mb-1"><strong>Address:</strong> <?= $enquiry->address ?></p>
              <p class="mb-1"><strong>Email:</strong> <?= $enquiry->email ?></p>
              <p class="mb-1"><strong>Phone:</strong> <?= $enquiry->phone ?></p>
            </div>

            <!-- Right: Enquiry Info -->
            <div class="col-md-6 text-md-end">
              <!-- <h6 class="fw-bold">Enquiry Info</h6> -->
              <p class="mb-1"><strong>Enquiry ID:</strong> <?= $enquiry->enquiry_id ?></p>
              <p class="mb-1"><strong>Date:</strong> <?= date("d M Y", strtotime($enquiry->created_at)) ?></p>
            </div>
          </div>
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0" id="enquiryItemTable">
              <thead class="table-dark">
                <tr>
                  <th>Sl. No</th>
                  <th>Product Name</th>
                  <th>Product Description</th>
                  <th>Quantity</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end mt-3">
            <a href="<?= base_url('admin/manage_enquiry') ?>" class="btn btn-secondary">Back to List</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

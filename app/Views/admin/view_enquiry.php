<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-dark text-white">
          <h5>Enquiry Details</h5>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
              <tr>
                <th>Enquiry ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Product Name</th>
                <th>Product Quantity</th>   
                <th>Date</th> 
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= $enquiry->enquiry_id ?></td>
                <td><?= $enquiry->name ?></td>
                <td><?= $enquiry->email ?></td>
                <td><?= $enquiry->product_name ?></td>
                <td><?= $enquiry->quantity ?></td>
                <td><?= date("d M Y", strtotime($enquiry->created_at)) ?></td> 
              </tr>
            </tbody>
          </table>

          <div class="text-end mt-3">
            <a href="<?= base_url('admin/manage_enquiry') ?>" class="btn btn-secondary">Back to List</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

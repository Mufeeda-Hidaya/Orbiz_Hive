<div class="container-fluid py-2">
  <div class="my-3"></div>
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
            <h5 class="text-white mb-0 ms-3">Edit Enquiry</h5>
          </div>
        </div>
        <div class="card-body">
          <div id="messageBox" class="alert d-none text-center" role="alert"></div>
          <form id="editEnquiryForm" class="p-3">
            <input type="hidden" name="enquiry_id" id="enquiry_id" value="<?= esc($enquiry['enquiry_id']) ?>">
            <div class="mb-3">
              <label for="product_name" class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
              <input type="text" name="product_name" id="product_name" class="form-control cursor-padding" value="<?= esc($enquiry['product_name']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="product_desc" class="form-label fw-bold">Product Description</label>
              <textarea name="product_desc" id="product_desc" class="form-control custom-textarea" rows="3"><?= isset($enquiry['product_desc']) ? esc($enquiry['product_desc']) : '' ?></textarea>
            </div>
            <div class="mb-3">
              <label for="quantity" class="form-label fw-bold">Quantity</label>
              <input type="number" name="quantity" id="quantity" class="form-control cursor-padding" value="<?= esc($enquiry['quantity']) ?>" required>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
              <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>
<style>
.custom-textarea {
    border: 1px solid #ced4da !important;  
    color: #000 !important;   
    padding-left: 10px;           
}
</style>

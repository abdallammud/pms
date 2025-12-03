<div class="modal fade" id="addGuaranteeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"> 
    <div class="modal-content">

      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold">Add New Guarantor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="app/guarantees.php?action=add" method="POST">

        <div class="modal-body">
          <div class="row g-3">

            <!-- LEFT SIDE -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Personal Details</h6>

                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="full_name" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Phone Number</label>
                  <input type="text" name="phone" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control">
                </div>
              </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Identification & Work</h6>

                <div class="mb-3">
                  <label class="form-label">ID Number</label>
                  <input type="text" name="id_number" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Work Information</label>
                  <input type="text" name="work_info" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>

              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-primary px-4">Save Guarantor</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>

    </div>
  </div>
</div>

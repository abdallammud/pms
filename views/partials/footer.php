    <div class="modal fade" id="themeModal" tabindex="-1" aria-labelledby="themeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="themeModalLabel">Theme Customization</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="themeForm">
                        <div class="mb-3">
                            <label for="primaryColor" class="form-label">Primary Color</label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control color-picker me-3" id="primaryColor"
                                    value="#0F3B6C">
                                <input type="text" class="form-control" id="primaryColorText" value="#0F3B6C">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="secondaryColor" class="form-label">Secondary Color</label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control color-picker me-3" id="secondaryColor"
                                    value="#FDB913">
                                <input type="text" class="form-control" id="secondaryColorText" value="#FDB913">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="textColor" class="form-label">Text Color</label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control color-picker me-3" id="textColor"
                                    value="#666666">
                                <input type="text" class="form-control" id="textColorText" value="#666666">
                            </div>
                        </div>
                        <div class="theme-preview" id="themePreview">
                            Theme Preview
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTheme">Save Changes</button>
                    <button type="button" class="btn btn-outline-danger" id="resetTheme">Reset to Default</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Jquery -->
    <script src="public/js/jquery.min.js"></script>
    <!-- Chart -->
    <script src="public/plugins/chartjs/js/chart.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="public/js/bootstrap.bundle.min.js"></script>
    <!-- Datatables -->
    <script src="public/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="public/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script src="public/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="public/js/toaster.js"></script>
    <script src="public/js/script.js"></script>
</body>

</html>
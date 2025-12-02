function loadModal(folder, file, targetId) {
    // Check if modal already exists in DOM
    if ($(targetId).length > 0) {
        var modal = new bootstrap.Modal(document.querySelector(targetId));
        modal.show();
        return;
    }

    // Show loading indicator if needed (optional)

    // Fetch modal content
    $.ajax({
        url: 'app/modal_loader.php',
        type: 'GET',
        data: { folder: folder, file: file },
        success: function (response) {
            $('body').append(response);
            var modal = new bootstrap.Modal(document.querySelector(targetId));
            modal.show();
        },
        error: function () {
            Swal.fire('Error', 'Failed to load modal.', 'error');
        }
    });
}

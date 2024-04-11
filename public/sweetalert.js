// app.js

// Function to display a success message
window.displaySuccessAlert = function (message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 5000,
    });
};

window.displayErrorAlert = function (message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        timer: 5000,
    });
};

// Function to display a confirmation dialog
window.displayConfirmationDialog = function (message, form) {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
};

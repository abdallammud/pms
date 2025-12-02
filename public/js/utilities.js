function isNumberKey(e, maxVal = '') {
    var charCode = (e.which) ? e.which : e.keyCode;
    if (charCode !== 8 && charCode !== 46 && !/\d/.test(String.fromCharCode(charCode))) {
    return false;
    }
    return true;
}
function isNumberKeyWihtLimit(e, maxVal = '') {
    var charCode = (e.which) ? e.which : e.keyCode;

    // Allow backspace and delete keys
    if (charCode === 8 || charCode === 46) {
        return true;
    }

    // Check if the input is a number
    if (!/\d/.test(String.fromCharCode(charCode))) {
        return false;
    }

    // Get the current input value and the new character being added
    var input = e.target;
    var currentValue = input.value;
    var newValue = currentValue + String.fromCharCode(charCode);

    // If maxVal is provided, ensure the new value does not exceed it
    if (maxVal !== '' && parseInt(newValue) > parseInt(maxVal)) {
        return false;
    }

    return true;
}

function isNumberOrCommaKey(e) {
    // Allow only numbers, period, comma, and delete keys
    var charCode = (e.which) ? e.which : e.keyCode;
    if (charCode !== 8 && charCode !== 46 && charCode !== 188 && !/[0-9,]/.test(String.fromCharCode(charCode))) {
    return false;
    }
    return true;
}
function clearErrors() {
    $('input, select, textarea').removeClass('error')
    $('.form-error').css('display', 'none')
}
function showError (msg, id) {
    let span = $('#'+id).parents('.form-group').find('.form-error');
    let span2 = $('#'+id).parents('.form-outline').find('.form-error');
    // let span3 = $('#'+id).parents('div').find('.form-error');
    let span4 = $('#'+id).parents('.div').find('.form-error');
    $(span).html(msg)
    $(span).show();

    $(span2).html(msg)
    $(span2).show();

    $(span4).html(msg)
    $(span4).show();

    $('#'+id).addClass('error')
}
function validateField(value, errorMessage, fieldId) {
    if (!value) {
        showError(errorMessage, fieldId);
        return false;
    }
    return true;
}
function isUserNameValid(username) {
    const res = /^[a-zA-Z0-9_\.]+$/.exec(username);
    const valid = !!res;
    return valid;
}
function isValidPhone(phone) {
    const res = /^[0-9-+]+$/.exec(phone);;
    const valid = !!res;
    return valid;
}
function isNumber(evt)  {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;

    return true;
}
function extractEmails ( text ){
    return text.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)/gi);
}
function formatDateRange(startDate, endDate) {
    // Parse input dates
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    // Define options for formatting
    const options = { month: 'short', day: 'numeric' };
    const yearOptions = { ...options, year: 'numeric' };
    
    // Format the dates
    const startFormatted = start.toLocaleDateString('en-US', options);
    const endFormatted = end.toLocaleDateString('en-US', yearOptions);

    // Check if the year is the same
    if (start.getFullYear() === end.getFullYear()) {
        // Same year
        return `${startFormatted} - ${endFormatted}`;
    } else {
        // Different years
        const startFormattedWithYear = `${startFormatted} ${start.getFullYear()}`;
        return `${startFormattedWithYear} - ${endFormatted}`;
    }
}
function formatDate(dateString, format = 'month_name', incDay = true) {
  const date = new Date(dateString);

  if (isNaN(date.getTime())) {
    // Invalid date, handle it as needed (e.g., return an error message)
    return "Invalid Date";
  }

  if (format === "dd/mm/yyyy") {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;   

  } else if (format === "month_name") {
    const months = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    let returnDate = `${day} ${months[date.getMonth()]}, ${year}`
    if(!incDay) returnDate = `${months[date.getMonth()]}, ${year}`
    return returnDate;// months[date.getMonth()];   

  } else {
    // Handle other formats or default to dd/mm/yyyy
    return formatDate(dateString, "dd/mm/yyyy");
  }
}
function validateForm(form) {
    let error = false; 
    if(form instanceof jQuery || form instanceof HTMLElement) {
        $(form).find('.validate').each(function() { 
            let input = $(this); 
            let value = input.val(); 
            let message = input.data('msg');
            // console.log(form)
            if(!message) message = "This is required";
            let id = input.attr('id');  
            error = !validateField(value, message, id) || error; 
        }); 
    }

    return error;
}
function form_loading(form, msg = 'Please wait..') { 
    // Get the submit button from the form 
    let submitButton = $(form).find('button[type="submit"]'); 
    // Replace the HTML of the submit button with the spinner 
    submitButton.html(`<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>  ${msg}`); 
    $(form).find('button').attr('disabled', true);
}
function form_loadingUndo(form) {
    // Get the submit button from the form
    let submitButton = $(form).find('button[type="submit"]');
    // Reset the submit button text to "Save" or "Apply" based on the form type
    let buttonText = $(form).hasClass('edit-form') ? 'Apply' : 'Save';
    // Restore the button text and enable all buttons
    submitButton.html(buttonText);
    $(form).find('button').attr('disabled', false);
    // Clear all input fields and textareas
    $(form).find('input:not([type="hidden"]), textarea').val('');
    // Reset select elements to their first option
    $(form).find('select').prop('selectedIndex', 0);
    // Clear any error messages
    clearErrors();
}
function formatMoney(amount, currencySymbol = '$', decimals = 2) {
    // Ensure the number is a valid value and round to the specified decimals
    amount = parseFloat(amount).toFixed(decimals);

    // Split the amount into integer and decimal parts
    let [integerPart, decimalPart] = amount.split('.');

    // Add thousands separator to the integer part
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // Combine integer part, decimal part and currency symbol
    return currencySymbol + integerPart + (decimalPart ? '.' + decimalPart : '');
}
function markAsRead(type) {
    let data = {type:type};
    $.post(`${base_url}/app/payroll_controller.php?action=update&endpoint=markAsRead`, data, function(data) {
        console.log(data)
        let res = JSON.parse(data);
        if(!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                location.reload();
            });
            console.log(res);
        }
    })
}

function downloadCSV(data, filename = "data.csv") {
    // Convert array of arrays into a CSV string
    let csvContent = data.map(row => row.map(item => `"${item}"`).join(",")).join("\r\n");

    // Create a Blob object from the CSV string
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });

    // Create a link element to initiate the download
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);

    // Append the link to the document and simulate a click
    document.body.appendChild(link);
    link.click();

    // Clean up: Remove the link and revoke the object URL
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function addPrefixToNumber(num, targetLength) {
  // Convert the number to a string
  let numString = String(num);

  // Use String.prototype.padStart() to add leading zeros
  // until the string reaches the targetLength.
  return numString.padStart(targetLength, '0');
}

function check_contractExpiredEmployees(page) {
    if (page == 'employees') {
       show_employee_contractExpiredModal();
    } else {
        // Redirect to hrm page
        window.location.href = `${base_url}/employee`;
    }
}

function show_employee_contractExpiredModal() {
    // send post request to app/hrm_controller.php?action=get&endpoint=showEmployeeContractExpiredModal
    $.post(`${base_url}/app/hrm_controller.php?action=get&endpoint=showEmployeeContractExpiredModal`, function(data) {
        console.log(data)
        let res = JSON.parse(data);
        console.log(res)
        let tbody = '';
        res.forEach(element => {
            tbody += `<tr>
                    <td>${element.staff_no}</td>
                    <td>${element.full_name}</td>
                    <td>${formatDate(element.contract_end)}</td>
                    <td>
                        <a href="${base_url}/employees/show/${element.employee_id}" class="fa smr-10 fa-eye" aria-hidden="true"></a>
                        <a href="${base_url}/employees/edit/${element.employee_id}" class="fa smr-10 fa-edit" aria-hidden="true"></a>
                    </td>
                </tr>`;
        });
        $('.contract-expired-body').html(tbody);
        // Destroy inital datatable and then
        if ($.fn.DataTable.isDataTable('#contractExpiredTable')) {
            $('#contractExpiredTable').DataTable().destroy();
        }
        // initialize datatables with no pagination just search bar and make sure it show all data not first 10
        $("#contractExpiredTable").DataTable({
            "lengthChange": false,
            "searching": true,
            "info": true,
            "autoWidth": false
        });
        $('#contractExpiredModal').modal('show');
    })
}
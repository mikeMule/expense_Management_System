// Main JavaScript functionality for Expense Management System
document.addEventListener('DOMContentLoaded', function () {
  const spinner = document.getElementById('loading-spinner')
  if (spinner) {
    // Hide the spinner once the page is fully loaded
    window.addEventListener('load', function () {
      spinner.style.opacity = '0'
      setTimeout(() => {
        spinner.style.display = 'none'
      }, 300) // Match this with CSS transition time
    })
  }
})
;(function () {
  // Tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  )
  tooltipTriggerList.forEach(function (el) {
    new bootstrap.Tooltip(el)
  })

  // Popovers
  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  )
  popoverTriggerList.forEach(function (el) {
    new bootstrap.Popover(el)
  })

  // Form validation
  var forms = document.querySelectorAll('.needs-validation')
  forms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault()
        e.stopPropagation()
      }
      form.classList.add('was-validated')
    })
  })

  // Auto-hide alerts
  var alerts = document.querySelectorAll('.alert:not(.alert-permanent)')
  alerts.forEach(function (alert) {
    setTimeout(function () {
      var bsAlert = new bootstrap.Alert(alert)
      bsAlert.close()
    }, 5000)
  })

  // Currency input formatting
  var currencyInputs = document.querySelectorAll('input[data-currency]')
  currencyInputs.forEach(function (input) {
    input.addEventListener('blur', function () {
      var value = parseFloat(this.value)
      if (!isNaN(value)) this.value = value.toFixed(2)
    })
  })

  // Search box for tables
  var searchInput = document.getElementById('searchInput')
  if (searchInput) {
    searchInput.addEventListener('keyup', function () {
      var searchTerm = this.value.toLowerCase()
      var tableRows = document.querySelectorAll('tbody tr')
      tableRows.forEach(function (row) {
        var text = row.textContent.toLowerCase()
        row.style.display = text.includes(searchTerm) ? '' : 'none'
      })
    })
  }

  // Date range picker
  var dateFilterForm = document.getElementById('dateFilterForm')
  if (dateFilterForm) {
    var startDate = document.getElementById('start_date')
    var endDate = document.getElementById('end_date')
    if (startDate && endDate) {
      startDate.addEventListener('change', function () {
        endDate.min = this.value
      })
      endDate.addEventListener('change', function () {
        startDate.max = this.value
      })
    }
  }

  // Export buttons
  var exportButtons = document.querySelectorAll('.btn-export')
  exportButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var spinner = this.querySelector('.spinner-border')
      var text = this.querySelector('.btn-text')
      if (spinner && text) {
        spinner.classList.remove('d-none')
        text.textContent = 'Exporting...'
        this.disabled = true
        setTimeout(() => {
          spinner.classList.add('d-none')
          text.textContent = this.getAttribute('data-original-text') || 'Export'
          this.disabled = false
        }, 2000)
      }
    })
  })

  // Profile Modal (custom)
  var profileBtn = document.getElementById('userProfileModalBtn')
  var customModal = document.getElementById('userProfileModalCustom')
  var closeBtn = document.getElementById('closeProfileModalCustom')
  if (profileBtn && customModal) {
    profileBtn.addEventListener('click', function (e) {
      e.preventDefault()
      customModal.style.display = 'flex'
      document.body.classList.add('modal-open')
    })
  }
  if (closeBtn && customModal) {
    closeBtn.addEventListener('click', function () {
      customModal.style.display = 'none'
      document.body.classList.remove('modal-open')
    })
  }
  if (customModal) {
    customModal.addEventListener('click', function (e) {
      if (e.target === customModal) {
        customModal.style.display = 'none'
        document.body.classList.remove('modal-open')
      }
    })
  }

  // Fallback: Ensure Bootstrap dropdowns are initialized if JS loads after DOM
  if (window.bootstrap) {
    document
      .querySelectorAll('.dropdown-toggle')
      .forEach(function (dropdownToggleEl) {
        if (!dropdownToggleEl.hasAttribute('data-bs-toggle-initialized')) {
          new bootstrap.Dropdown(dropdownToggleEl)
          dropdownToggleEl.setAttribute('data-bs-toggle-initialized', 'true')
        }
      })
  }
})()

// Utility functions
function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Chart creation functions
function createPieChart(canvasId, data, labels) {
  const ctx = document.getElementById(canvasId)
  if (!ctx) return

  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [
        {
          data: data,
          backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#FF6384',
            '#C9CBCF',
          ],
          borderWidth: 2,
          borderColor: '#fff',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
        },
      },
    },
  })
}

function createLineChart(canvasId, incomeData, expenseData, labels) {
  const ctx = document.getElementById(canvasId)
  if (!ctx) return

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Income',
          data: incomeData,
          borderColor: '#28a745',
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          fill: true,
          tension: 0.4,
        },
        {
          label: 'Expenses',
          data: expenseData,
          borderColor: '#dc3545',
          backgroundColor: 'rgba(220, 53, 69, 0.1)',
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return '$' + value.toLocaleString()
            },
          },
        },
      },
      plugins: {
        legend: {
          position: 'top',
        },
      },
    },
  })
}

function createBarChart(canvasId, data, labels, label) {
  const ctx = document.getElementById(canvasId)
  if (!ctx) return

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: label,
          data: data,
          backgroundColor: '#007bff',
          borderColor: '#0056b3',
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return '$' + value.toLocaleString()
            },
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
      },
    },
  })
}

// AJAX helper function
function makeRequest(url, method = 'GET', data = null) {
  return fetch(url, {
    method: method,
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: data ? JSON.stringify(data) : null,
  }).then((response) => {
    if (!response.ok) {
      throw new Error('Network response was not ok')
    }
    return response.json()
  })
}

// Show loading state
function showLoading(element) {
  element.innerHTML =
    '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
}

// Show error message
function showError(message, container = null) {
  const alert = document.createElement('div')
  alert.className = 'alert alert-danger alert-dismissible fade show'
  alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  if (container) {
    container.insertBefore(alert, container.firstChild)
  } else {
    document
      .querySelector('.container, .container-fluid')
      .insertBefore(
        alert,
        document.querySelector('.container, .container-fluid').firstChild
      )
  }
}

// Show success message
function showSuccess(message, container = null) {
  const alert = document.createElement('div')
  alert.className = 'alert alert-success alert-dismissible fade show'
  alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  if (container) {
    container.insertBefore(alert, container.firstChild)
  } else {
    document
      .querySelector('.container, .container-fluid')
      .insertBefore(
        alert,
        document.querySelector('.container, .container-fluid').firstChild
      )
  }
}

function showCornerNotification(message, type = 'success') {
  const toastContainer = document.getElementById('toastContainer')
  if (!toastContainer) return

  const toastId = 'toast-' + Date.now()
  const toastIcon =
    type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'
  const toastHeaderClass =
    type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'

  const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="toast-header ${toastHeaderClass}">
                <i class="fas ${toastIcon} me-2"></i>
                <strong class="me-auto">${
                  type === 'success' ? 'Success' : 'Error'
                }</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `

  toastContainer.insertAdjacentHTML('beforeend', toastHTML)
  const toastElement = document.getElementById(toastId)
  const toast = new bootstrap.Toast(toastElement)
  toast.show()

  toastElement.addEventListener('hidden.bs.toast', () => {
    toastElement.remove()
  })
}

function showCenterNotification(message, type = 'success') {
  const notification = document.getElementById('center-notification')
  if (!notification) return

  const icon =
    type === 'success'
      ? '<i class="fas fa-check-circle text-success"></i>'
      : '<i class="fas fa-exclamation-triangle text-danger"></i>'

  notification.innerHTML = `<div class="icon">${icon}</div><div>${message}</div>`
  notification.classList.add('show')

  setTimeout(() => {
    notification.classList.remove('show')
  }, 3000) // Notification will be visible for 3 seconds
}

// Employee Page Logic
document.addEventListener('DOMContentLoaded', function () {
  // Auto-generate email from first name in Add Employee modal
  const firstNameInput = document.querySelector(
    '#addEmployee2025Modal #first_name'
  )
  const emailInput = document.querySelector('#addEmployee2025Modal #email')

  if (firstNameInput && emailInput) {
    emailInput.readOnly = true // Make the email field non-editable
    emailInput.classList.add('bg-light') // Visually indicate it's disabled

    firstNameInput.addEventListener('input', function () {
      const firstName = this.value.trim().toLowerCase().replace(/\s+/g, '.')
      if (firstName) {
        emailInput.value = `${firstName}@mulewave.com`
      } else {
        emailInput.value = ''
      }
    })
  }

  // Initialize DataTables for employee table
  if (document.getElementById('employeeTable')) {
    const employeeTable = $('#employeeTable').DataTable({
      responsive: true,
      pageLength: 10,
      language: {
        search: "<i class='fas fa-search'></i>",
        searchPlaceholder: 'Search employees...',
      },
      columnDefs: [
        { targets: [0], className: 'fw-bold text-primary' },
        { targets: '_all', className: 'align-middle' },
      ],
    })

    // Search functionality
    const searchInput = document.getElementById('searchInput')
    if (searchInput) {
      searchInput.addEventListener('keyup', function () {
        employeeTable.search(this.value).draw()
      })
    }

    // Status filter
    const statusFilter = document.getElementById('statusFilter')
    if (statusFilter) {
      statusFilter.addEventListener('change', function () {
        employeeTable.column(6).search(this.value).draw()
      })
    }
  }

  // Add Employee Modal (2025)
  const openBtn = document.getElementById('openAddEmployee2025Modal')
  const modal2025 = document.getElementById('addEmployee2025Modal')
  const closeBtn = document.getElementById('closeAddEmployee2025Modal')
  const closeBtnFooter = document.getElementById(
    'closeAddEmployee2025ModalFooter'
  )

  function openModal() {
    if (modal2025) {
      modal2025.classList.remove('hide')
      modal2025.style.display = 'flex'
    }
  }

  function closeModal() {
    if (modal2025) {
      modal2025.classList.add('hide')
      setTimeout(() => {
        modal2025.style.display = 'none'
      }, 300)
    }
  }

  if (openBtn) {
    openBtn.addEventListener('click', openModal)
  }
  if (closeBtn) {
    closeBtn.addEventListener('click', closeModal)
  }
  if (closeBtnFooter) {
    closeBtnFooter.addEventListener('click', closeModal)
  }

  // Close on overlay click
  if (modal2025) {
    modal2025.addEventListener('click', function (e) {
      if (e.target === modal2025) {
        closeModal()
      }
    })
  }

  // Initialize Flatpickr for hire date
  if (document.getElementById('hire_date')) {
    flatpickr('#hire_date', {
      altInput: true,
      altFormat: 'F j, Y',
      dateFormat: 'Y-m-d',
      defaultDate: 'today',
    })
  }

  // Handle Add Employee form submission with AJAX
  const addEmployeeForm = document.querySelector('#addEmployee2025Modal form')
  if (addEmployeeForm) {
    addEmployeeForm.addEventListener('submit', function (e) {
      e.preventDefault()
      const formData = new FormData(this)
      const submitButton = this.querySelector('button[type="submit"]')
      const originalButtonText = submitButton.innerHTML

      submitButton.disabled = true
      submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...`

      fetch('actions/add_employee.php', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && data.employee) {
            const employeeTable = $('#employeeTable').DataTable()

            const statusDropdownHtml = `
              <select class="form-select form-select-sm employee-status-select status-select-active" data-employee-id="${data.employee.id}" data-original-status="active">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <div class="spinner-border spinner-border-sm text-primary ms-2 d-none" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            `

            const newRow = employeeTable.row
              .add([
                `<strong>${data.employee.employee_id}</strong>`,
                `<div><strong>${data.employee.first_name} ${data.employee.last_name}</strong><br><small class="text-muted">${data.employee.email}</small></div>`,
                `<span class="badge bg-secondary">${data.employee.position}</span>`,
                data.employee.phone
                  ? `<i class="fas fa-phone me-1"></i>${data.employee.phone}`
                  : '-',
                `<strong class="text-success">$${parseFloat(
                  data.employee.monthly_salary
                ).toFixed(2)}</strong>`,
                new Date(data.employee.hire_date).toLocaleDateString('en-US', {
                  month: 'short',
                  day: 'numeric',
                  year: 'numeric',
                }),
                statusDropdownHtml,
                `<div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-info btn-view-details" data-employee-id="${data.employee.id}" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <a href="edit_employee.php?id=${data.employee.id}" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="delete_employee.php?id=${data.employee.id}" class="btn btn-outline-danger btn-delete" data-item="employee '${data.employee.first_name} ${data.employee.last_name}'" title="Delete"><i class="fas fa-trash"></i></a>
             </div>`,
              ])
              .draw(false)
              .node()

            $(newRow).find('td:eq(0)').addClass('fw-bold text-primary')
            $(newRow).addClass('align-middle')

            closeModal()
            showCornerNotification(data.message, 'success')
          } else {
            showCornerNotification(
              data.message || 'An unexpected error occurred.',
              'danger'
            )
          }
        })
        .catch((error) => {
          showCornerNotification('Network error. Please try again.', 'danger')
          console.error('Error:', error)
        })
        .finally(() => {
          submitButton.disabled = false
          submitButton.innerHTML = originalButtonText
          addEmployeeForm.reset()
          addEmployeeForm.classList.remove('was-validated')
        })
    })
  }

  // Handle View Details button click
  $('#employeeTable tbody').on('click', '.btn-view-details', function () {
    const employeeId = $(this).data('employee-id')
    const modal = new bootstrap.Modal(
      document.getElementById('employeeDetailsModal')
    )
    const modalBody = document.getElementById('employeeDetailsContent')

    modalBody.innerHTML =
      '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
    modal.show()

    fetch(`actions/get_employee_details.php?id=${employeeId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const { employee, salaries } = data
          let salaryHtml = '<p>No salary payments found for this employee.</p>'
          if (salaries.length > 0) {
            salaryHtml = `
                            <table class="table table-sm table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `
            salaries.forEach((s) => {
              salaryHtml += `
                                <tr>
                                    <td>${new Date(
                                      s.year,
                                      s.month - 1
                                    ).toLocaleString('en-US', {
                                      month: 'long',
                                    })}</td>
                                    <td>${s.year}</td>
                                    <td>$${parseFloat(s.amount).toFixed(2)}</td>
                                    <td><span class="badge bg-${
                                      s.status === 'paid'
                                        ? 'success'
                                        : 'warning'
                                    }">${s.status}</span></td>
                                    <td>${
                                      s.payment_date
                                        ? new Date(
                                            s.payment_date
                                          ).toLocaleDateString()
                                        : 'N/A'
                                    }</td>
                                </tr>
                            `
            })
            salaryHtml += '</tbody></table>'
          }

          modalBody.innerHTML = `
                        <h4>${employee.first_name} ${employee.last_name}</h4>
                        <p><strong>ID:</strong> ${employee.employee_id}</p>
                        <p><strong>Position:</strong> ${employee.position}</p>
                        <hr>
                        <h5>Salary History</h5>
                        ${salaryHtml}
                    `
        } else {
          modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`
        }
      })
      .catch((err) => {
        modalBody.innerHTML = `<div class="alert alert-danger">An error occurred while fetching details.</div>`
        console.error(err)
      })
  })

  // Handle employee status change
  $('#employeeTable tbody').on(
    'change',
    '.employee-status-select',
    function () {
      const select = $(this)
      const employeeId = select.data('employee-id')
      const newStatus = select.val()
      const originalStatus = select.data('original-status')
      const spinner = select.next('.spinner-border')

      spinner.removeClass('d-none')
      select.prop('disabled', true)

      const formData = new FormData()
      formData.append('employee_id', employeeId)
      formData.append('status', newStatus)

      fetch('actions/update_employee_status.php', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showCenterNotification(data.message, 'success')
            select.data('original-status', newStatus) // Update original status

            // Toggle classes for color change
            select.removeClass('status-select-active status-select-inactive')
            if (newStatus === 'active') {
              select.addClass('status-select-active')
            } else {
              select.addClass('status-select-inactive')
            }

            // Explicitly set the selected attribute for persistent state
            select.find('option').removeAttr('selected')
            select
              .find(`option[value="${newStatus}"]`)
              .attr('selected', 'selected')

            // Correctly update the DataTable's cell data with the modified HTML
            const table = $('#employeeTable').DataTable()
            const cell = select.closest('td')
            table.cell(cell).data(select[0].outerHTML).draw(false)
          } else {
            showCenterNotification(data.message, 'danger')
            select.val(originalStatus) // Revert on failure
          }
        })
        .catch((error) => {
          showCenterNotification(
            'An error occurred while updating status.',
            'danger'
          )
          select.val(originalStatus) // Revert on failure
          console.error('Error:', error)
        })
        .finally(() => {
          spinner.addClass('d-none')
          select.prop('disabled', false)
        })
    }
  )

  // Handle salary deletion confirmation with custom modal
  document.addEventListener('click', function (e) {
    const deleteButton = e.target.closest('.btn-delete-salary')
    const modalElement = document.getElementById('deleteSalaryConfirmModal')

    if (deleteButton && modalElement) {
      const deleteUrl = deleteButton.dataset.deleteUrl
      const itemName = deleteButton.dataset.itemName

      const confirmBtn = modalElement.querySelector('#confirmDeleteBtn')
      const itemNameElement = modalElement.querySelector('#deleteItemName')

      confirmBtn.href = deleteUrl
      if (itemNameElement) {
        itemNameElement.textContent = `Item: ${itemName}`
      }

      modalElement.classList.remove('hide')
      modalElement.style.display = 'flex'
    }

    // Handle closing the custom modal
    if (e.target.closest('[data-dismiss-2025="modal"]') && modalElement) {
      modalElement.classList.add('hide')
      setTimeout(() => {
        modalElement.style.display = 'none'
      }, 300)
    }
  })
})

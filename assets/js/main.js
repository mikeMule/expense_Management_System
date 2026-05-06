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
    // Sidebar Toggle for Mobile (Tailwind Implementation)
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const toggleIcon = document.getElementById('toggleIcon');

    if (sidebarToggle && sidebar && sidebarOverlay) {
        const toggleSidebar = () => {
            const isOpen = sidebar.classList.contains('left-0') && !sidebar.classList.contains('left-[-16rem]');
            
            if (isOpen) {
                // Close
                sidebar.classList.replace('left-0', 'left-[-16rem]');
                sidebarOverlay.classList.add('opacity-0');
                setTimeout(() => sidebarOverlay.classList.add('hidden'), 300);
                toggleIcon.classList.replace('fa-times', 'fa-bars');
            } else {
                // Open
                sidebar.classList.replace('left-[-16rem]', 'left-0');
                sidebarOverlay.classList.remove('hidden');
                setTimeout(() => sidebarOverlay.classList.remove('opacity-0'), 10);
                toggleIcon.classList.replace('fa-bars', 'fa-times');
            }
        };

        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // Close sidebar when a link is clicked on mobile
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) { // lg breakpoint in Tailwind
                    toggleSidebar();
                }
            });
        });
    }
  }
})
;(function () {
  // Tailwind does not have built-in tooltips/popovers.
  // For basic tooltips, we can rely on standard 'title' attributes or build custom JS,
  // but for this migration, we'll remove the Bootstrap JS initializers.

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

  // Auto-hide alerts (custom Tailwind implementation)
  var alerts = document.querySelectorAll('.alert-auto-dismiss')
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 300);
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
  var searchInputs = [document.getElementById('searchInput'), document.getElementById('salaryTableSearch'), document.getElementById('employeeSearchInput')]
  searchInputs.forEach(function(searchInput) {
      if (searchInput) {
        searchInput.addEventListener('keyup', function () {
          var searchTerm = this.value.toLowerCase()
          // Find the closest table body. For a global search, search all tbodys.
          var tableRows = document.querySelectorAll('tbody tr')
          tableRows.forEach(function (row) {
            var text = row.textContent.toLowerCase()
            row.style.display = text.includes(searchTerm) ? '' : 'none'
          })
        })
      }
  });

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

  // Modal Handlers (Generic)
  window.openModal = function(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
          modal.classList.remove('hidden');
          setTimeout(() => {
              modal.querySelector('.bg-white')?.classList.remove('scale-95', 'opacity-0');
              modal.querySelector('.fixed.inset-0.bg-gray-900\\/50')?.classList.remove('opacity-0');
          }, 10);
      }
  };

  window.closeModal = function(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
          modal.querySelector('.bg-white')?.classList.add('scale-95', 'opacity-0');
          modal.querySelector('.fixed.inset-0.bg-gray-900\\/50')?.classList.add('opacity-0');
          setTimeout(() => {
              modal.classList.add('hidden');
          }, 300); // match transition duration
      }
  };
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
  alert.className = 'bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-xl flex items-center alert-auto-dismiss transition-opacity duration-300'
  alert.innerHTML = `
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <p class="text-red-700 text-sm font-medium flex-grow">${message}</p>
        <button type="button" class="text-red-400 hover:text-red-600 focus:outline-none" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `
  if (container) {
    container.insertBefore(alert, container.firstChild)
  } else {
    document.querySelector('.main-content').insertBefore(alert, document.querySelector('.main-content').firstChild)
  }
}

// Show success message
function showSuccess(message, container = null) {
  const alert = document.createElement('div')
  alert.className = 'bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-r-xl flex items-center alert-auto-dismiss transition-opacity duration-300'
  alert.innerHTML = `
        <i class="fas fa-check-circle text-green-500 mr-3"></i>
        <p class="text-green-700 text-sm font-medium flex-grow">${message}</p>
        <button type="button" class="text-green-400 hover:text-green-600 focus:outline-none" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `
  if (container) {
    container.insertBefore(alert, container.firstChild)
  } else {
    document.querySelector('.main-content').insertBefore(alert, document.querySelector('.main-content').firstChild)
  }
}

function showCornerNotification(message, type = 'success') {
  const toastContainer = document.getElementById('toastContainer')
  if (!toastContainer) return

  const toastId = 'toast-' + Date.now()
  const toastIcon = type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'
  const bgColor = type === 'success' ? 'bg-white border-green-500' : 'bg-white border-red-500'
  const textColor = 'text-gray-800'

  const toastHTML = `
        <div id="${toastId}" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow border-l-4 ${bgColor} transition-opacity duration-300 opacity-0 transform translate-x-full" role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100">
                <i class="fas ${toastIcon}"></i>
            </div>
            <div class="ml-3 text-sm font-normal text-gray-800">${message}</div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" onclick="document.getElementById('${toastId}').remove()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `

  toastContainer.insertAdjacentHTML('beforeend', toastHTML)
  const toastElement = document.getElementById(toastId)
  
  // Trigger animation
  setTimeout(() => {
      toastElement.classList.remove('opacity-0', 'translate-x-full');
  }, 10);

  // Auto remove
  setTimeout(() => {
      toastElement.classList.add('opacity-0');
      setTimeout(() => toastElement.remove(), 300);
  }, 5000)
}

function showCenterNotification(message, type = 'success') {
  const notification = document.getElementById('center-notification')
  if (!notification) return

  const icon = type === 'success' ? '<i class="fas fa-check-circle text-green-500 text-3xl"></i>' : '<i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>'

  notification.className = 'fixed top-[10%] left-1/2 transform -translate-x-1/2 z-[10000] bg-white rounded-2xl shadow-2xl p-6 flex items-center gap-4 border border-gray-100 transition-all duration-300 opacity-0 -translate-y-4';
  notification.innerHTML = `<div>${icon}</div><div class="text-lg font-semibold text-gray-800">${message}</div>`
  
  // Trigger animation
  setTimeout(() => {
      notification.classList.remove('opacity-0', '-translate-y-4');
  }, 10);

  setTimeout(() => {
    notification.classList.add('opacity-0', '-translate-y-4');
  }, 3000)
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
        search: '',
        searchPlaceholder: 'Search employees...',
        emptyTable: '<div class="py-10 text-center"><div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200"><i class="fas fa-users text-gray-300"></i></div><h3 class="text-gray-400 font-bold text-sm">No employees found in the directory.</h3></div>'
      },
      columnDefs: [
        { targets: [0, 1, 2, 3, 4], className: 'align-middle' },
        { targets: [3], className: 'text-right' },
        { targets: [2, 4, 5], className: 'text-center' },
        { targets: [5], orderable: false }
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
    if (statusFilter) {
      statusFilter.addEventListener('change', function () {
        employeeTable.column(4).search(this.value).draw()
      })
    }
  }

    // Initialize DataTables for transaction table
    if (document.getElementById('transactionTable')) {
        const transactionTable = $('#transactionTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'desc']], // Sort by Date descending by default
            language: {
                search: "<i class='fas fa-search'></i>",
                searchPlaceholder: 'Search transactions...',
            },
            columnDefs: [
                { targets: [0], className: 'font-medium' },
                { targets: '_all', className: 'align-middle' },
            ],
        })

        const searchInput = document.getElementById('search')
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                transactionTable.search(this.value).draw()
            })
        }

        // Toggle description Read More/Less
        $(document).on('click', '.toggle-description', function () {
            const container = $(this).closest('.description-container')
            const shortDesc = container.find('.short-desc')
            const fullDesc = container.find('.full-desc')
            const isExpanded = !fullDesc.hasClass('hidden')

            if (isExpanded) {
                fullDesc.addClass('hidden')
                shortDesc.removeClass('hidden')
                $(this).text('More')
            } else {
                fullDesc.removeClass('hidden')
                shortDesc.addClass('hidden')
                $(this).text('Less')
            }
        })
    }

    // Initialize DataTables for salary table
    if (document.getElementById('salaryTable')) {
        const salaryTable = $('#salaryTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[1, 'desc']], 
            language: {
                search: '',
                searchPlaceholder: 'Search salaries...',
                emptyTable: '<div class="py-10 text-center"><div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200"><i class="fas fa-money-check-alt text-gray-300"></i></div><h3 class="text-gray-400 font-bold text-sm">No salary records found.</h3></div>'
            },
            columnDefs: [
                { targets: '_all', className: 'align-middle' },
                { targets: [3], className: 'text-right' },
                { targets: [2, 4], className: 'text-center' },
                { targets: [4], orderable: false }
            ],
        })

        const salarySearchInput = document.getElementById('salarySearchInput')
        if (salarySearchInput) {
            salarySearchInput.addEventListener('keyup', function () {
                salaryTable.search(this.value).draw()
            })
        }
    }

    // Initialize DataTables for pending salaries table
    if (document.getElementById('pendingSalariesTable')) {
        const pendingTable = $('#pendingSalariesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[2, 'desc'], [1, 'desc']], // Year then month descending
            language: {
                search: "<i class='fas fa-search'></i>",
                searchPlaceholder: 'Search pending...',
            },
            columnDefs: [
                { targets: '_all', className: 'align-middle' },
            ],
        })

        const pendingSearchInput = document.getElementById('pendingSearchInput')
        if (pendingSearchInput) {
            pendingSearchInput.addEventListener('keyup', function () {
                pendingTable.search(this.value).draw()
            })
        }
    }

    // Initialize DataTables for user table
    if (document.getElementById('userTable')) {
        const userTable = $('#userTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[4, 'desc']], // Created date descending
            language: {
                search: "<i class='fas fa-search'></i>",
                searchPlaceholder: 'Search users...',
            },
            columnDefs: [
                { targets: '_all', className: 'align-middle' },
            ],
        })

        const userSearchInput = document.getElementById('userSearchInput')
        if (userSearchInput) {
            userSearchInput.addEventListener('keyup', function () {
                userTable.search(this.value).draw()
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

  if (openBtn) {
    openBtn.addEventListener('click', () => window.openModal('addEmployee2025Modal'))
  }

  const closeEmployeeModal = () => {
      window.closeModal('addEmployee2025Modal');
      setTimeout(() => {
          const form = modal2025.querySelector('form');
          if (form) {
              form.reset();
              form.classList.remove('was-validated');
          }
      }, 300);
  };

  if (closeBtn) closeBtn.addEventListener('click', closeEmployeeModal);
  if (closeBtnFooter) closeBtnFooter.addEventListener('click', closeEmployeeModal);

  // Close on backdrop click (we added the backdrop element specifically)
  const addEmployeeBackdrop = document.getElementById('addEmployeeModalBackdrop');
  if (addEmployeeBackdrop) {
      addEmployeeBackdrop.addEventListener('click', closeEmployeeModal);
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

      // Check form validation
      if (!this.checkValidity()) {
        e.stopPropagation()
        this.classList.add('was-validated')
        showCenterNotification(
          'Please fill in all required fields correctly.',
          'danger'
        )
        return
      }

      const formData = new FormData(this)

      // Ensure email field has a value if it's empty
      const emailInput = this.querySelector('#email')
      if (emailInput && !emailInput.value.trim()) {
        const firstNameInput = this.querySelector('#first_name')
        if (firstNameInput && firstNameInput.value.trim()) {
          const firstName = firstNameInput.value
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '.')
          emailInput.value = `${firstName}@mulewave.com`
          formData.set('email', emailInput.value)
        }
      }

      const submitButton = this.querySelector('button[type="submit"]')
      const originalButtonText = submitButton.innerHTML

      submitButton.disabled = true
      submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...`

      // Debug: Log form data
      console.log('Form data being sent:', Object.fromEntries(formData))

      fetch('actions/add_employee.php', {
        method: 'POST',
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error('Network response was not ok')
          }
          return response.json()
        })
        .then((data) => {
          console.log('Response data:', data) // Debug log
          if (data.success && data.employee) {
            // Show success notification
            showCenterNotification(
              data.message || 'Employee added successfully!',
              'success'
            )
            // Refresh the entire page to show updated data after a short delay
            setTimeout(() => {
              window.location.reload()
            }, 1500)
          } else {
            showCenterNotification(
              data.message || 'An unexpected error occurred.',
              'danger'
            )
          }
        })
        .catch((error) => {
          showCenterNotification('Network error. Please try again.', 'danger')
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
    const modalBody = document.getElementById('employeeDetailsContent')

    modalBody.innerHTML =
      '<div class="flex justify-center p-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div></div>'
    
    window.openModal('employeeDetailsModal');

    fetch(`actions/get_employee_details.php?id=${employeeId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const { employee, salaries } = data
          let salaryHtml = '<p class="text-gray-500 text-sm">No salary payments found for this employee.</p>'
          if (salaries.length > 0) {
            salaryHtml = `
                            <div class="overflow-x-auto rounded-xl border border-gray-200">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                        <tr>
                                            <th class="px-4 py-3">Month</th>
                                            <th class="px-4 py-3">Year</th>
                                            <th class="px-4 py-3">Amount</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3">Payment Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                        `
            salaries.forEach((s) => {
              salaryHtml += `
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-800">${new Date(
                                      s.year,
                                      s.month - 1
                                    ).toLocaleString('en-US', {
                                      month: 'long',
                                    })}</td>
                                    <td class="px-4 py-3 text-gray-800">${s.year}</td>
                                    <td class="px-4 py-3 font-bold text-gray-800">$${parseFloat(s.amount).toFixed(2)}</td>
                                    <td class="px-4 py-3"><span class="px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                      s.status === 'paid'
                                        ? 'bg-green-100 text-green-800 border border-green-200'
                                        : 'bg-yellow-100 text-yellow-800 border border-yellow-200'
                                    }">${s.status}</span></td>
                                    <td class="px-4 py-3 text-gray-500">${
                                      s.payment_date
                                        ? new Date(
                                            s.payment_date
                                          ).toLocaleDateString()
                                        : 'N/A'
                                    }</td>
                                </tr>
                            `
            })
            salaryHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `
          }

          modalBody.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Name</p>
                                <p class="font-bold text-gray-800 text-base">${employee.first_name} ${employee.last_name}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Employee ID</p>
                                <p class="font-bold text-gray-800 text-base">${employee.employee_id}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Email</p>
                                <p class="text-gray-800">${employee.email || 'N/A'}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Phone</p>
                                <p class="text-gray-800">${employee.phone || 'N/A'}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Position</p>
                                <p class="text-gray-800">${employee.position}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Monthly Salary</p>
                                <p class="font-bold text-green-600">$${parseFloat(employee.monthly_salary).toFixed(2)}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Hire Date</p>
                                <p class="text-gray-800">${
                                  employee.hire_date
                                    ? new Date(employee.hire_date).toLocaleDateString()
                                    : 'N/A'
                                }</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-1 uppercase tracking-wider text-xs font-semibold">Status</p>
                                <p><span class="px-2.5 py-0.5 rounded-full text-xs font-medium border ${
                                  employee.status === 'active' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-800 border-gray-200'
                                }">${employee.status}</span></p>
                            </div>
                            <div class="md:col-span-2 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-gray-500 mb-2 uppercase tracking-wider text-xs font-semibold">Attachment</p>
                                ${
                                  employee.attachment_path
                                    ? `<a href="${employee.attachment_path}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-white border border-brand text-brand hover:bg-brand hover:text-white rounded-lg text-sm font-medium transition-colors"><i class="fas fa-file-pdf mr-2"></i>View Document</a>`
                                    : '<span class="text-gray-400 italic">No attachment</span>'
                                }
                            </div>
                        </div>
                        <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Salary History</h5>
                        ${salaryHtml}
                    `
        } else {
          modalBody.innerHTML = `<div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm">${data.message}</div>`
        }
      })
      .catch((err) => {
        modalBody.innerHTML = `<div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm">An error occurred while fetching details.</div>`
        console.error(err)
      })
  })

  // Close handlers for Employee Details Modal
  const closeDetailsModalBtn = document.getElementById('closeEmployeeDetailsModal');
  const closeDetailsModalFooterBtn = document.getElementById('closeEmployeeDetailsModalFooter');
  const detailsModalBackdrop = document.getElementById('employeeDetailsModalBackdrop');

  if (closeDetailsModalBtn) closeDetailsModalBtn.addEventListener('click', () => window.closeModal('employeeDetailsModal'));
  if (closeDetailsModalFooterBtn) closeDetailsModalFooterBtn.addEventListener('click', () => window.closeModal('employeeDetailsModal'));
  if (detailsModalBackdrop) detailsModalBackdrop.addEventListener('click', () => window.closeModal('employeeDetailsModal'));

  // Handle employee status change
  $('#employeeTable tbody').on(
    'change',
    '.employee-status-select',
    function () {
      const select = $(this)
      const employeeId = select.data('employee-id')
      const newStatus = select.val()
      const originalStatus = select.data('original-status')
      const spinner = select.siblings('.spinner-border')

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

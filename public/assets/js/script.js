document.addEventListener('DOMContentLoaded', () => {
  // Sidebar toggle
  const toggleBtn = document.getElementById('toggleBtn');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');

  if (toggleBtn && sidebar && mainContent) {
    toggleBtn.addEventListener('click', () => {
      const isMobile = window.innerWidth <= 768;

      if (isMobile) {
        sidebar.classList.toggle('active'); // Mobile slide-in
      } else {
        sidebar.classList.toggle('collapsed'); // Desktop collapse
        mainContent.classList.toggle('expanded');
        toggleBtn.classList.toggle('collapsed'); // Appended for styling
      }
    });

    document.addEventListener('click', (e) => {
      if (
        window.innerWidth <= 768 &&
        !sidebar.contains(e.target) &&
        !toggleBtn.contains(e.target)
      ) {
        sidebar.classList.remove('active');
      }
    });
  }

  // Submenu toggle on click
  document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      const submenu = toggle.nextElementSibling;

      // Optional: Close other open submenus
      document.querySelectorAll('.submenu').forEach(menu => {
        if (menu !== submenu) {
          menu.classList.remove('open');
        }
      });

      if (submenu && submenu.classList.contains('submenu')) {
        submenu.classList.toggle('open');
      }

      // Rotate arrow if exists
      const arrow = toggle.querySelector('.arrow');
      if (arrow) {
        arrow.classList.toggle('rotated');
      }
    });
  });

 
  

  // Product & stock list filtering
  const searchInput = document.getElementById('searchInput');
  const brandFilter = document.getElementById('brandFilter');
  const table = document.getElementById('productTable');
  const autocompleteList = document.getElementById('autocomplete-list');

  if (searchInput && brandFilter && table) {
    function filterTable() {
      const filterText = searchInput.value.toLowerCase();
      const selectedBrand = brandFilter.value.toLowerCase();

      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const brand = row.cells[2].textContent.toLowerCase();
        const model = row.cells[3].textContent.toLowerCase();
        const matchSearch = brand.includes(filterText) || model.includes(filterText);
        const matchBrand = selectedBrand === '' || brand === selectedBrand;

        row.style.display = matchSearch && matchBrand ? '' : 'none';
      });
    }

    function showAutocompleteSuggestions() {
      const inputVal = searchInput.value.toLowerCase();
      autocompleteList.innerHTML = '';
      if (!inputVal) return;

      const suggestions = new Set();
      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const brand = row.cells[2].textContent.toLowerCase();
        const model = row.cells[3].textContent.toLowerCase();
        if (brand.startsWith(inputVal)) suggestions.add(brand);
        if (model.startsWith(inputVal)) suggestions.add(model);
      });

      suggestions.forEach(s => {
        const div = document.createElement('div');
        div.textContent = s;
        div.onclick = () => {
          searchInput.value = s;
          autocompleteList.innerHTML = '';
          filterTable();
        };
        autocompleteList.appendChild(div);
      });
    }

    searchInput.addEventListener('keyup', () => {
      filterTable();
      showAutocompleteSuggestions();
    });

    brandFilter.addEventListener('change', filterTable);

    document.addEventListener('click', (e) => {
      if (e.target !== searchInput) {
        autocompleteList.innerHTML = '';
      }
    });
  }

  // Bulk upload
  const fileInput = document.getElementById("bulk-file");
  const statusText = document.getElementById("upload-status");

  if (fileInput && statusText) {
    fileInput.addEventListener("change", async () => {
      const file = fileInput.files[0];
      if (!file) return;

      statusText.textContent = "Uploading...";

      const formData = new FormData();
      formData.append("file", file);

      try {
        const response = await fetch("/upload", { method: "POST", body: formData });
        if (response.ok) {
          statusText.textContent = "Upload successful ✅";
          statusText.style.color = "green";
        } else {
          statusText.textContent = "Upload failed ❌";
          statusText.style.color = "red";
        }
      } catch (err) {
        console.error(err);
        statusText.textContent = "Upload error ❌";
        statusText.style.color = "red";
      }
    });
  }

  // Submit stock form
  window.submitForm = function () {
    const product = document.getElementById('productSelect').value;
    const warehouses = document.getElementsByName('warehouse[]');
    const stocks = document.getElementsByName('stock[]');

    const stockData = [];
    for (let i = 0; i < warehouses.length; i++) {
      stockData.push({
        warehouse: warehouses[i].value,
        stock: stocks[i].value
      });
    }

    console.log({ product, stockData });
    alert("Stock has been added successfully");
  }

  // Filter options for custom dropdown
  window.filterOptions = function () {
    const input = document.getElementById('productSelect');
    const filter = input.value.toLowerCase();
    const options = document.querySelectorAll('#customOptions li');
    const dropdown = document.getElementById('customOptions');

    let hasVisible = false;
    options.forEach(option => {
      const text = option.textContent.toLowerCase();
      option.style.display = text.includes(filter) ? '' : 'none';
      if (text.includes(filter)) hasVisible = true;
    });

    dropdown.style.display = hasVisible ? 'block' : 'none';
  }

  window.selectOption = function (elem) {
    document.getElementById('productSelect').value = elem.textContent;
    document.getElementById('customOptions').style.display = 'none';
  }

  // Table search input
  const input = document.getElementById("tableSearchInput");
  const productTableBody = document.getElementById("productTable")?.getElementsByTagName("tbody")[0];

  if (input && productTableBody) {
    input.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase();
      Array.from(productTableBody.rows).forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? "" : "none";
      });
    });
  }
});



//for profile and notification bell
  const profileIcon = document.getElementById("profileIcon");
  const profileDropdown = document.getElementById("profileDropdown");

  const notificationBell = document.getElementById("notificationBell");
  const notificationPanel = document.getElementById("notificationPanel");
  const closeNotification = document.getElementById("closeNotification");

  // Toggle profile dropdown
  profileIcon.addEventListener("click", () => {
    const isVisible = profileDropdown.style.display === "block";
    profileDropdown.style.display = isVisible ? "none" : "block";
    notificationPanel.style.display = "none"; // Close notification panel if open
  });

  // Toggle notification panel
  notificationBell.addEventListener("click", () => {
    const isVisible = notificationPanel.style.display === "block";
    notificationPanel.style.display = isVisible ? "none" : "block";
    profileDropdown.style.display = "none"; // Close profile dropdown if open
  });

  // Close notification panel
  closeNotification.addEventListener("click", () => {
    notificationPanel.style.display = "none";
  });

  // Click outside to close dropdown/panel
  document.addEventListener("click", (event) => {
    if (!event.target.closest("#profileIcon") && !event.target.closest("#profileDropdown")) {
      profileDropdown.style.display = "none";
    }
    if (!event.target.closest("#notificationBell") && !event.target.closest("#notificationPanel")) {
      notificationPanel.style.display = "none";
    }
  });
// ==========================================
// GLOBAL VARIABLES
// ==========================================
let HOURS = [];
let FLOOR_TABLES = [];

// ==========================================
// SIDEBAR & UI CONTROLS
// ==========================================
const sidebar = document.getElementById('sidebar');
const menuToggle = document.getElementById('menuToggle');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const logoutBtn = document.getElementById('logoutBtn');

function toggleSidebar() {
  sidebar.classList.toggle('active');
  sidebarOverlay.classList.toggle('active');
}

if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);

if (logoutBtn) {
  logoutBtn.addEventListener('click', function() {
    if (confirm('Are you sure you want to log out?')) {
      window.location.href = 'logout.php';
    }
  });
}

window.addEventListener('resize', function() {
  if (window.innerWidth > 768) {
    sidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
  }
});

// ==========================================
// DATE & FLOOR CONTROLS
// ==========================================
const dateFilter = document.getElementById('dateFilter');
if (dateFilter) {
  dateFilter.addEventListener('change', function() {
    const currentFloor = new URLSearchParams(window.location.search).get('floor') || '1';
    window.location.href = '?date=' + this.value + '&floor=' + currentFloor;
  });
}

function changeFloor(floor) {
  const currentDate = document.getElementById('dateFilter').value;
  window.location.href = '?date=' + currentDate + '&floor=' + floor;
}

// ==========================================
// TOAST NOTIFICATION
// ==========================================
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  const toastMessage = document.getElementById('toastMessage');
  const toastIcon = document.getElementById('toastIcon');
  
  toastMessage.textContent = message;
  toastIcon.textContent = type === 'success' ? '✓' : '✕';
  toast.className = 'toast active ' + type;
  
  setTimeout(() => {
    toast.classList.remove('active');
  }, 4000);
}

// ==========================================
// MODAL CONTROLS
// ==========================================
function openAddModal() {
  document.getElementById('addModal').classList.add('active');
}

function closeAddModal() {
  document.getElementById('addModal').classList.remove('active');
  document.getElementById('addForm').reset();
}

function openBookingModal(table, hour) {
  console.log('📝 Opening booking modal for:', table, 'at', hour);
  openAddModal();
  document.getElementById('addTable').value = table;
  document.getElementById('addHour').value = hour;
}

function closeDetailModal() {
  document.getElementById('detailModal').classList.remove('active');
}

function openEditModal() {
  closeDetailModal();
  document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
  document.getElementById('editModal').classList.remove('active');
  document.getElementById('editForm').reset();
}

// ==========================================
// ADD RESERVATION FORM
// ==========================================
const addForm = document.getElementById('addForm');
if (addForm) {
  addForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    console.log('🚀 Submitting reservation...');
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Adding...';
    submitBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('table', document.getElementById('addTable').value);
    formData.append('hour', document.getElementById('addHour').value);
    formData.append('name', document.getElementById('addName').value);
    formData.append('phone', document.getElementById('addPhone').value);
    formData.append('guests', document.getElementById('addGuests').value);
    formData.append('email', document.getElementById('addEmail').value);
    formData.append('date', document.getElementById('addDate').value);
    formData.append('notes', document.getElementById('addNotes').value);
    
    try {
      const response = await fetch('reservation_handler.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      console.log('📥 Server response:', result);
      
      if (result.success) {
        showToast(result.message, 'success');
        closeAddModal();
        
        console.log('✅ Calling addReservationToGrid with:', result.data);
        addReservationToGrid(result.data);
        
      } else {
        showToast(result.message, 'error');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    } catch (error) {
      console.error('❌ Error:', error);
      showToast('Error: ' + error.message, 'error');
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  });
}

// ==========================================
// ADD RESERVATION TO GRID
// ==========================================
function addReservationToGrid(data) {
  console.log('🔄 Adding reservation to grid:', data);
  
  const table = data.table;
  const hour = parseInt(data.hour);
  
  const tableIndex = FLOOR_TABLES.indexOf(table);
  
  if (tableIndex === -1) {
    console.warn(`⚠️ Table ${table} not in current floor`);
    showToast('✅ Reservation added! Switch to the correct floor to see it.', 'success');
    setTimeout(() => location.reload(), 2000);
    return;
  }
  
  const hourIndex = HOURS.indexOf(hour);
  
  if (hourIndex === -1) {
    console.error(`❌ Hour ${hour} not found in hours array`);
    setTimeout(() => location.reload(), 2000);
    return;
  }
  
  const allCells = document.querySelectorAll('.cell');
  console.log('📊 Total cells:', allCells.length);
  
  const cellIndex = (tableIndex * HOURS.length) + hourIndex;
  console.log('🎯 Target cell index:', cellIndex);
  
  const targetCell = allCells[cellIndex];
  
  if (!targetCell) {
    console.error(`❌ Cell not found at index ${cellIndex}`);
    setTimeout(() => location.reload(), 2000);
    return;
  }
  
  console.log('✅ Target cell found:', targetCell);
  
  targetCell.classList.remove('empty');
  targetCell.setAttribute('onclick', `openDetailModal(${data.id})`);
  
  const statusClass = {
    'pending': 'st-pending',
    'confirmed': 'st-confirmed',
    'seated': 'st-seated',
    'cancelled': 'st-cancelled'
  }[data.status] || 'st-pending';
  
  targetCell.innerHTML = `
    <div class="res-block ${statusClass}" style="animation: slideIn 0.4s ease;">
      <div class="res-name">${escapeHtml(data.name)}</div>
      <div class="res-pax">👥 ${data.guests} guest${data.guests > 1 ? 's' : ''}</div>
    </div>
  `;
  
  targetCell.style.boxShadow = '0 0 0 3px rgba(250, 193, 217, 0.5)';
  setTimeout(() => {
    targetCell.style.boxShadow = '';
  }, 1500);
  
  console.log('✅ Grid updated successfully!');
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// ==========================================
// VIEW DETAIL RESERVATION
// ==========================================
async function openDetailModal(id) {
  document.getElementById('detailModal').classList.add('active');
  document.getElementById('detailContent').innerHTML = '<p style="text-align: center; color: var(--gray-medium);">Loading...</p>';
  
  const formData = new FormData();
  formData.append('action', 'get');
  formData.append('id', id);
  
  try {
    const response = await fetch('reservation_handler.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      const data = result.data;
      const statusClass = data.status === 'pending' ? 'st-pending' : 
                         data.status === 'confirmed' ? 'st-confirmed' : 
                         data.status === 'seated' ? 'st-seated' : 'st-cancelled';
      
      const statusText = data.status.charAt(0).toUpperCase() + data.status.slice(1);
      
      document.getElementById('detailContent').innerHTML = `
        <div class="detail-row">
          <span class="detail-label">Guest Name:</span>
          <span class="detail-value">${data.name}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Phone:</span>
          <span class="detail-value">${data.phone}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Email:</span>
          <span class="detail-value">${data.email || '-'}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Table:</span>
          <span class="detail-value">Table ${data.table}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Date & Time:</span>
          <span class="detail-value">${data.date} at ${String(data.hour).padStart(2, '0')}:00</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Guests:</span>
          <span class="detail-value">${data.guests} people</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Status:</span>
          <span class="detail-value">
            <span class="status-badge ${statusClass}">${statusText}</span>
          </span>
        </div>
        <div class="modal-actions" style="margin-top: 24px;">
          <button class="btn btn-danger" onclick="deleteReservation(${data.id})">Delete</button>
          <button class="btn btn-secondary" onclick="loadEditModal(${data.id})">Edit</button>
          <button class="btn btn-primary" onclick="closeDetailModal()">Close</button>
        </div>
      `;
    } else {
      document.getElementById('detailContent').innerHTML = `<p style="text-align: center; color: var(--red-accent);">${result.message}</p>`;
    }
  } catch (error) {
    showToast('Error loading details', 'error');
    document.getElementById('detailContent').innerHTML = '<p style="text-align: center; color: var(--red-accent);">Failed to load reservation details</p>';
  }
}

// ==========================================
// LOAD EDIT MODAL
// ==========================================
async function loadEditModal(id) {
  const formData = new FormData();
  formData.append('action', 'get');
  formData.append('id', id);
  
  try {
    const response = await fetch('reservation_handler.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      const data = result.data;
      document.getElementById('editId').value = data.id;
      document.getElementById('editName').value = data.name;
      document.getElementById('editPhone').value = data.phone;
      document.getElementById('editEmail').value = data.email || '';
      document.getElementById('editGuests').value = data.guests;
      document.getElementById('editStatus').value = data.status;
      document.getElementById('editNotes').value = data.notes || '';
      
      openEditModal();
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Error loading edit form', 'error');
  }
}

// ==========================================
// UPDATE RESERVATION
// ==========================================
const editForm = document.getElementById('editForm');
if (editForm) {
  editForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', document.getElementById('editId').value);
    formData.append('name', document.getElementById('editName').value);
    formData.append('phone', document.getElementById('editPhone').value);
    formData.append('email', document.getElementById('editEmail').value);
    formData.append('guests', document.getElementById('editGuests').value);
    formData.append('status', document.getElementById('editStatus').value);
    formData.append('notes', document.getElementById('editNotes').value);
    
    try {
      const response = await fetch('reservation_handler.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success) {
        showToast(result.message, 'success');
        closeEditModal();
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast(result.message, 'error');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    } catch (error) {
      showToast('Error: ' + error.message, 'error');
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  });
}

// ==========================================
// DELETE RESERVATION
// ==========================================
async function deleteReservation(id) {
  if (!confirm('Are you sure you want to delete this reservation? This action cannot be undone.')) return;
  
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('id', id);
  
  try {
    const response = await fetch('reservation_handler.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      closeDetailModal();
      setTimeout(() => location.reload(), 1500);
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Error: ' + error.message, 'error');
  }
}

// ==========================================
// KEYBOARD SHORTCUTS
// ==========================================
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeAddModal();
    closeDetailModal();
    closeEditModal();
  }
});

console.log('✅ Bitehive Reservation System Ready!');
let myReservations = [];

document.addEventListener('DOMContentLoaded', function () {
    updateTableOptions();
    loadReservations();
    setInterval(loadReservations, 15000);

    document.getElementById('bookingFloor').addEventListener('change', updateTableOptions);

    document.getElementById('bookingForm').addEventListener('submit', handleBookingSubmit);

    document.getElementById('logoutBtn')?.addEventListener('click', function () {
        if (confirm('Are you sure you want to log out?')) {
            localStorage.removeItem('sidebarOpen');
            localStorage.removeItem('activeMenu');
            window.location.href = "../../auth/controllers/logout.php";
        }
    });
});

function updateTableOptions() {
    const floor = document.getElementById('bookingFloor').value;
    const tables = FLOOR_DATA[floor] || [];
    const select = document.getElementById('bookingTable');
    select.innerHTML = '<option value="">Select table...</option>';
    
    ALL_ROOMS.forEach(room => {
        if (tables.includes(room.id_reservation_room)) {
            const opt = document.createElement('option');
            opt.value = room.id_reservation_room;
            opt.textContent = `Table ${room.id_reservation_room} (${room.seats} seats)`;
            select.appendChild(opt);
        }
    });
}

async function loadReservations() {
    try {
        const fd = new FormData();
        fd.append('action', 'fetch_my_reservations');
        const res = await fetch('handler.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            myReservations = data.data;
            displayReservations();
        } else {
            document.getElementById('reservationList').innerHTML = '<div class="empty-state">No reservations found</div>';
        }
    } catch (e) {
        document.getElementById('reservationList').innerHTML = '<div class="empty-state">Error loading data</div>';
    }
}

function displayReservations() {
    const el = document.getElementById('reservationList');
    if (myReservations.length === 0) {
        el.innerHTML = '<div class="empty-state">No reservations yet. Book your first table!</div>';
        return;
    }
    el.innerHTML = myReservations.map(r => `
        <div class="res-card ${r.status}">
            <div class="res-header">
                <div class="res-table">Table ${r.table}</div>
                <span class="status-badge ${r.status}">${r.status}</span>
            </div>
            <div class="res-info">
                <div>👤 ${r.name}</div>
                <div>📅 ${r.date} at ${r.time}</div>
                <div>👥 ${r.guests} guests</div>
                <div>📞 ${r.phone}</div>
            </div>
            ${(r.status === 'pending' || r.status === 'confirmed') ? 
                `<button class="btn-cancel" onclick="cancelReservation(${r.id})">Cancel Reservation</button>` : ''}
        </div>`
    ).join('');
}

async function cancelReservation(id) {
    if (!confirm('Are you sure you want to cancel this reservation?')) return;
    try {
        const fd = new FormData();
        fd.append('action', 'cancel_reservation');
        fd.append('id', id);
        const res = await fetch('handler.php', { method: 'POST', body: fd });
        const data = await res.json();
        alert(data.message);
        if (data.success) loadReservations();
    } catch (e) {
        alert('Error cancelling reservation');
    }
}

async function handleBookingSubmit(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Booking...';
    
    try {
        const fd = new FormData();
        fd.append('action', 'add_reservation');
        fd.append('table', document.getElementById('bookingTable').value);
        fd.append('hour', document.getElementById('bookingHour').value);
        fd.append('date', document.getElementById('bookingDate').value);
        fd.append('guests', document.getElementById('bookingGuests').value);
        fd.append('phone', document.getElementById('bookingPhone').value);
        fd.append('email', document.getElementById('bookingEmail').value);
        fd.append('name', document.getElementById('bookingName').value);

        const res = await fetch('handler.php', { method: 'POST', body: fd });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            e.target.reset();
            document.getElementById('bookingDate').value = new Date().toISOString().split('T')[0];
            updateTableOptions();
            loadReservations();
        }
    } catch (e) {
        alert('Error creating reservation');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Book Reservation';
    }
}
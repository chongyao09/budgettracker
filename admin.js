// Load dashboard statistics
function loadStats() {
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_stats'
    })
    .then(response => response.json())
    .then(data => {
        // Update statistics cards
        document.querySelector('.stat-card:nth-child(1) .value').textContent = data.total_users.toLocaleString();
        document.querySelector('.stat-card:nth-child(2) .value').textContent = data.active_users.toLocaleString();
        document.querySelector('.stat-card:nth-child(3) .value').textContent = data.total_transactions.toLocaleString();
        document.querySelector('.stat-card:nth-child(4) .value').textContent = '$' + data.avg_transaction.toLocaleString();
        
        // Update user growth chart
        updateUserGrowthChart(data.user_growth);
    })
    .catch(error => console.error('Error loading stats:', error));
}

// Update user growth chart
function updateUserGrowthChart(data) {
    const labels = data.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleString('default', { month: 'short' });
    });
    const values = data.map(item => item.count);
    
    if (window.userGrowthChart) {
        window.userGrowthChart.destroy();
    }
    
    const ctx = document.getElementById('userGrowthChart').getContext('2d');
    window.userGrowthChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'New Users',
                data: values,
                fill: true,
                backgroundColor: 'rgba(255, 166, 0, 0.1)',
                borderColor: '#ffa600',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Load users dynamically and display in the table
function loadUsers() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_users'
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';
        data.users.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td><button class="btn btn-primary" onclick="deleteUser(${user.id})">Delete</button></td>
            `;
            tbody.appendChild(tr);
        });
    })
    .catch(error => {
        console.error('Error loading users:', error);
    });
}

function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_user&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadUsers();
        } else {
            alert('Error deleting user: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error deleting user.');
        console.error('Error:', error);
    });
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadUsers();
    
    // Refresh stats every 5 minutes
    setInterval(loadStats, 300000);
}); 
<?php
session_start();
include("connector.php");

$username = $_SESSION['Username'];
$query = "SELECT PROFILE_PIC, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['MIDNAME'] . ' ' . $row['LASTNAME']);
} else {
    $profile_pic = 'default.jpg';
    $user_name = 'Admin';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>View Sit-in Reports</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

html, body {
    background: #0a192f;
    display: flex;
    flex-direction: column;
    width: 100%;
    color: #a0aec0;
}

/* Top Navigation Bar Styles */
.top-nav {
    background-color: #1a2942;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-left img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.nav-left .user-name {
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
}

.nav-right {
    display: flex;
    gap: 15px;
}

.nav-right a {
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 8px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.nav-right a i {
    font-size: 1rem;
}

.nav-right a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.nav-right .logout-button {
    background: rgba(220, 53, 69, 0.1);
    margin-left: 10px;
}

.nav-right .logout-button:hover {
    background: rgba(220, 53, 69, 0.2);
}

.content {
    margin-top: 80px;
    padding: 30px;
    min-height: calc(100vh - 80px);
    width: 100%;
}

/* Remove old sidebar styles */
.sidebar {
    display: none;
}

.container {
    background: #1a2942;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    height: calc(100vh - 60px);
    max-width: 1400px;
    margin: 0 auto;
    overflow: hidden;
}

.header {
    background: #1a2942;
    position: sticky;
    top: 0;
    z-index: 10;
    padding: 5px 0;
}

.header h1 {
    color: white;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 25px;
}

.search-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
    background: #2a3b55;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.export-buttons {
    display: flex;
    gap: 8px;
}

.export-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9em;
    font-weight: 500;
}

.csv {
    background: #28a745;
    color: white;
}

.excel {
    background: #217346;
    color: white;
}

.pdf {
    background: #dc3545;
    color: white;
}

.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    filter: brightness(110%);
}

.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

.search-box input {
    padding: 12px 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    width: 250px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 1rem;
    transition: all 0.2s;
}

.search-box input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.search-box input:focus {
    border-color: #4a90e2;
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.search-box button {
    background: #4a90e2;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.search-box button:hover {
    background: #357abd;
    transform: translateY(-2px);
}

.filter-select {
    padding: 12px 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    min-width: 140px;
    cursor: pointer;
    font-size: 1rem;
}

.filter-select:focus {
    border-color: #4a90e2;
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.filter-select option {
    background: #1a2942;
    color: white;
}

/* Table Styles */
.table-container {
    background: #1a2942;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 20px;
    height: calc(100vh - 250px);
    overflow-y: auto;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    color: #a0aec0;
}

thead {
    position: sticky;
    top: 0;
    z-index: 1;
}

th {
    background: #2a3b55;
    color: white;
    padding: 15px;
    font-weight: 500;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

td {
    padding: 12px 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

tbody tr:hover {
    background: rgba(255, 255, 255, 0.05);
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #0a192f;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #2a3b55;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #4a90e2;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .container {
        margin: 0 15px;
    }
    
    .search-container {
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex-wrap: wrap;
    }
}

@media (max-width: 768px) {
    .top-nav {
        flex-direction: column;
        padding: 10px;
    }
    
    .nav-left {
        margin-bottom: 10px;
    }
    
    .nav-right {
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .nav-right a {
        font-size: 0.8rem;
        padding: 6px 10px;
    }
    
    .content {
        margin-top: 120px;
    }
    
    .search-container {
        flex-direction: column;
    }
    
    .search-box {
        width: 100%;
        margin-left: 0;
    }
    
    .search-box input {
        width: 100%;
    }
    
    .export-buttons {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
</head>
<body>
<div class="top-nav">
    <div class="nav-left">
        <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.src='assets/default.png';">
        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
    </div>
    <div class="nav-right">
        <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
        <a href="addaily.php"><i class="fas fa-calendar-day"></i> Daily Records</a>
        <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
        <a href="adreservation.php"><i class="fas fa-calendar-check"></i> Reservations</a>
        <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
        <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback</a>
        <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</div>

<div class="content">
    <div class="container">
        <div class="header">
            <h1>Sit-in Reports</h1>            
        </div>
        <div class="search-container">
            <div class="export-buttons">
                <button class="export-btn csv" onclick="exportTableToCSV()">
                    <i class="fas fa-file-csv"></i> CSV
                </button>
                <button class="export-btn excel" onclick="exportTableToExcel()">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="export-btn pdf" onclick="exportTableToPDF()">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
            
            <div class="filter-group">
                <select id="purposeFilter" class="filter-select">
                    <option value="">All Purposes</option>
                    <option value="C Programming">C Programming</option>
                    <option value="Java Programming">Java Programming</option>
                    <option value="C# Programming">C# Programming</option>
                    <option value="System Integration & Architecture">System Integration & Architecture</option>
                    <option value="Embedded System & IoT">Embedded System & IoT</option>
                    <option value="Digital logic & Design">Digital logic & Design</option>
                    <option value="Computer Application">Computer Application</option>
                    <option value="Database">Database</option>
                    <option value="Project Management">Project Management</option>
                    <option value="Python Programming">Python Programming</option>
                    <option value="Mobile Application">Mobile Application</option>
                    <option value="Others...">Others...</option>
                </select>
                
                <select id="labFilter" class="filter-select">
                    <option value="">All Labs</option>
                    <?php
                    $lab_query = "SELECT DISTINCT LAB_ROOM FROM login_records WHERE LAB_ROOM IS NOT NULL ORDER BY LAB_ROOM";
                    $lab_result = mysqli_query($con, $lab_query);
                    while ($lab = mysqli_fetch_assoc($lab_result)) {
                        echo "<option value='" . htmlspecialchars($lab['LAB_ROOM']) . "'>" . htmlspecialchars($lab['LAB_ROOM']) . "</option>";
                    }
                    ?>
                </select>
                
                <select id="yearFilter" class="filter-select">
                    <option value="">All Years</option>
                    <?php
                    $current_year = date('Y');
                    for ($year = $current_year; $year >= $current_year - 5; $year--) {
                        echo "<option value='$year'>$year</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by ID, Name...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Purpose</th>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                    </tr>
                </thead>
                <tbody id="sitinTable">
                    <?php
                    $sitin_query = "SELECT 
                        lr.IDNO, 
                        lr.FULLNAME, 
                        lr.PURPOSE, 
                        lr.LAB_ROOM, 
                        DATE(lr.TIME_IN) as DATE,
                        TIME_FORMAT(TIME(lr.TIME_IN), '%h:%i %p') as TIME_IN_ONLY,
                        TIME_FORMAT(TIME(lr.TIME_OUT), '%h:%i %p') as TIME_OUT_ONLY
                    FROM login_records lr
                    WHERE lr.TIME_OUT IS NOT NULL 
                        AND lr.TIME_OUT != '0000-00-00 00:00:00'
                    ORDER BY DATE(lr.TIME_IN) DESC";
                    
                    $sitin_result = mysqli_query($con, $sitin_query);
                    if (mysqli_num_rows($sitin_result) > 0) {
                        while ($sitin_row = mysqli_fetch_assoc($sitin_result)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sitin_row['IDNO']); ?></td>
                                <td><?php echo htmlspecialchars($sitin_row['FULLNAME']); ?></td>
                                <td class="purpose-column"><?php echo htmlspecialchars($sitin_row['PURPOSE']); ?></td>
                                <td class="room-column"><?php echo htmlspecialchars($sitin_row['LAB_ROOM']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($sitin_row['DATE'])); ?></td>
                                <td><?php echo htmlspecialchars($sitin_row['TIME_IN_ONLY']); ?></td>
                                <td><?php echo htmlspecialchars($sitin_row['TIME_OUT_ONLY']); ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                <i class="fas fa-info-circle"></i> 
                                No completed sit-in records available. Records will appear here after students are logged out.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('purposeFilter').addEventListener('change', filterTable);
document.getElementById('labFilter').addEventListener('change', filterTable);
document.getElementById('yearFilter').addEventListener('change', filterTable);

function filterTable() {
    let searchValue = document.getElementById('searchInput').value.toLowerCase();
    let purposeValue = document.getElementById('purposeFilter').value.toLowerCase();
    let labValue = document.getElementById('labFilter').value.toLowerCase();
    let selectedYear = document.getElementById('yearFilter').value;
    
    document.querySelectorAll('#sitinTable tr').forEach(row => {
        let rowText = row.innerText.toLowerCase();
        let purposeMatch = purposeValue === '' || row.querySelector('td:nth-child(3)').innerText.toLowerCase() === purposeValue;
        let labMatch = labValue === '' || row.querySelector('td:nth-child(4)').innerText.toLowerCase() === labValue;
        let searchMatch = rowText.includes(searchValue);
        
        // Year filtering
        let yearMatch = true;
        if (selectedYear) {
            let rowDateText = row.querySelector('td:nth-child(5)').innerText; // e.g. "Mar 21, 2025"
            let rowYear = new Date(rowDateText).getFullYear().toString();
            yearMatch = rowYear === selectedYear;
        }
        
        row.style.display = purposeMatch && labMatch && searchMatch && yearMatch ? '' : 'none';
    });
}

function exportTableToCSV() {
    const table = document.querySelector('table');
    let csv = [];
    
    // Add styled header information with centering
    csv.push('"                                                                  "');
    csv.push('"                              UNIVERSITY OF CEBU - MAIN                              "');
    csv.push('"                            COLLEGE OF COMPUTER STUDIES                            "');
    csv.push('"              COMPUTER LABORATORY SITIN MONITORING SYSTEM REPORT                    "');
    csv.push('"                                                                  "');
    csv.push(''); // Empty line for spacing
    
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => `"${th.innerText}"`);
    csv.push(headers.join(','));
    
    // Only get visible rows (filtered results)
    const visibleRows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
    
    visibleRows.forEach(row => {
        const rowData = Array.from(row.querySelectorAll('td')).map(cell => `"${cell.innerText.replace(/"/g, '""')}"`);
        csv.push(rowData.join(','));
    });

    const csvFile = new Blob(['\ufeff' + csv.join('\n')], { type: 'text/csv;charset=utf-8' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'sitin_filtered_records.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
}

function exportTableToExcel() {
    try {
        const table = document.querySelector('table');
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText);
        const visibleRows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
        
        // Create new workbook with only filtered data
        const wb = XLSX.utils.book_new();
        const wsData = [
            [''],  // Space for logo
            [''],  // Space for logo
            [''],  // Space for logo
            ['                         UNIVERSITY OF CEBU - MAIN      '],
            ['                        COLLEGE OF COMPUTER STUDIES     '],
            ['             COMPUTER LABORATORY SITIN MONITORING SYSTEM REPORT       '],
            [''],  // Empty line for spacing
            headers
        ];
        
        visibleRows.forEach(row => {
            const rowData = Array.from(row.querySelectorAll('td')).map(cell => cell.innerText);
            wsData.push(rowData);
        });
        
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        
        // Set column widths
        ws['!cols'] = [
            { wch: 15 }, // ID Number
            { wch: 30 }, // Full Name
            { wch: 40 }, // Purpose
            { wch: 15 }, // Room
            { wch: 15 }, // Date
            { wch: 15 }, // Time In
            { wch: 15 }  // Time Out
        ];
        
        // Style the header
        ws['!merges'] = [
            { s: { r: 3, c: 0 }, e: { r: 3, c: 6 } },  // University name
            { s: { r: 4, c: 0 }, e: { r: 4, c: 6 } },  // College name
            { s: { r: 5, c: 0 }, e: { r: 5, c: 6 } }   // Report title
        ];
        
        // Add cell styles for header
        for (let i = 3; i <= 5; i++) {
            const cell = XLSX.utils.encode_cell({r: i, c: 0});
            if (!ws[cell]) ws[cell] = {};
            ws[cell].s = {
                font: { bold: true, color: { rgb: "14569B" } },  // UC Blue
                alignment: { horizontal: "center" }
            };
        }
        
        XLSX.utils.book_append_sheet(wb, ws, 'Filtered Sit-in Records');
        XLSX.writeFile(wb, 'sitin_filtered_records.xlsx');
    } catch (error) {
        console.error('Error exporting to Excel:', error);
        alert('There was an error exporting to Excel. Please try again.');
    }
}

function exportTableToPDF() {
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4');

        // Get table data
        const table = document.querySelector('table');
        const visibleRows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
        
        // Prepare data for autoTable
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText);
        const data = visibleRows.map(row => 
            Array.from(row.querySelectorAll('td')).map(cell => cell.innerText)
        );

        // Add title and header
        doc.setFontSize(16);
        doc.text('UNIVERSITY OF CEBU - MAIN', doc.internal.pageSize.width / 2, 20, { align: 'center' });
        doc.setFontSize(14);
        doc.text('COLLEGE OF COMPUTER STUDIES', doc.internal.pageSize.width / 2, 30, { align: 'center' });
        doc.text('COMPUTER LABORATORY SITIN MONITORING SYSTEM REPORT', doc.internal.pageSize.width / 2, 40, { align: 'center' });

        // Add filters information if any are applied
        const purposeFilter = document.getElementById('purposeFilter').value;
        const labFilter = document.getElementById('labFilter').value;
        const yearFilter = document.getElementById('yearFilter').value;
        
        let filterText = [];
        if (purposeFilter) filterText.push(`Purpose: ${purposeFilter}`);
        if (labFilter) filterText.push(`Lab: ${labFilter}`);
        if (yearFilter) filterText.push(`Year: ${yearFilter}`);
        
        if (filterText.length > 0) {
            doc.setFontSize(10);
            doc.text('Filters: ' + filterText.join(' | '), 14, 50);
        }

        // Add the table
        doc.autoTable({
            head: [headers],
            body: data,
            startY: 60,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 2,
            },
            headStyles: {
                fillColor: [26, 41, 66],
                textColor: 255,
                fontSize: 9,
                fontStyle: 'bold',
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245],
            },
            margin: { top: 60 },
        });

        // Add footer with page numbers
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(
                `Page ${i} of ${pageCount}`,
                doc.internal.pageSize.width - 20,
                doc.internal.pageSize.height - 10,
                { align: 'right' }
            );
            doc.text(
                '© University of Cebu - Main',
                20,
                doc.internal.pageSize.height - 10
            );
        }

        // Save the PDF
        doc.save('sitin_records.pdf');

    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('There was an error generating the PDF. Please try again.');
    }
}
</script>
</body>
</html>
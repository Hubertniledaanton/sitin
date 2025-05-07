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
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    display: flex;
    flex-direction: column;
    width: 100%;
}

/* Top Navigation Bar Styles */
.top-nav {
    background-color: rgba(42, 63, 95, 0.9);
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
    background: #f0f2f5;
}

/* Remove old sidebar styles */
.sidebar {
    display: none;
}

.container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    height: calc(100vh - 60px);
    max-width: 1400px;
    margin: 0 auto;
    overflow: hidden;
}

.header {
    background: white;
    position: sticky;
    top: 0;
    z-index: 10;
    padding: 5px 0;
}

.header h1 {
    color: #14569b;
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
    background: white;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

.search-box input {
    padding: 8px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    width: 250px;
    transition: all 0.3s;
}

.search-box button {
    background: #14569b;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.search-box button:hover {
    background: #0f4578;
    transform: translateY(-2px);
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    min-width: 140px;
    cursor: pointer;
}

.filter-select:focus,
.search-box input:focus {
    border-color: #14569b;
    outline: none;
    box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
}

.table-container {
    height: calc(100% - 180px);
    overflow-y: auto;
    border-radius: 12px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

thead {
    position: sticky;
    top: 0;
    z-index: 2;
}

th {
    background: #14569b;
    color: white;
    padding: 15px;
    font-weight: 500;
    text-align: left;
}

td {
    padding: 12px 15px;
    border-bottom: 1px solid #e2e8f0;
}

tbody tr:hover {
    background: #f8fafc;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #14569b;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #0f4578;
}

/* Responsive Design */
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
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
        
        // Set font and size
        doc.setFont('helvetica', 'bold');
        
        // Center everything on the page
        const pageWidth = doc.internal.pageSize.width;
        const pageCenter = pageWidth / 2;
        
        // Modern color scheme
        const primaryColor = [41, 128, 185];    // Modern Blue
        const secondaryColor = [52, 152, 219];  // Lighter Blue
        const accentColor = [231, 76, 60];      // Red Accent
        const textColor = [44, 62, 80];         // Dark Text
        const lightGray = [236, 240, 241];      // Light Gray for backgrounds
        
        // Add decorative header background
        doc.setFillColor(...primaryColor);
        doc.rect(0, 0, pageWidth, 60, 'F');
        
        // Add UC logo
        doc.addImage('assets/UC.png', 'PNG', 20, 15, 30, 30);
        
        // Add header text with modern styling
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(24);
        doc.text('UNIVERSITY OF CEBU - MAIN', pageCenter, 25, { align: 'center' });
        
        doc.setFontSize(18);
        doc.text('COLLEGE OF COMPUTER STUDIES', pageCenter, 35, { align: 'center' });
        
        doc.setFontSize(14);
        doc.text('COMPUTER LABORATORY SITIN MONITORING SYSTEM REPORT', pageCenter, 45, { align: 'center' });
        
        // Add decorative line
        doc.setDrawColor(...accentColor);
        doc.setLineWidth(0.5);
        doc.line(20, 65, pageWidth - 20, 65);
        
        // Get current date and time with modern formatting
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        const timeStr = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        // Add date and time with modern styling
        doc.setFontSize(10);
        doc.setTextColor(...textColor);
        doc.text(`Generated on: ${dateStr} at ${timeStr}`, pageCenter, 75, { align: 'center' });
        
        // Get current filter values
        const purposeFilter = document.getElementById('purposeFilter').value;
        const labFilter = document.getElementById('labFilter').value;
        const searchValue = document.getElementById('searchInput').value;
        const selectedYear = document.getElementById('yearFilter').value;
        
        // Add filter information with modern styling
        if (purposeFilter || labFilter || searchValue || selectedYear) {
            doc.setFontSize(9);
            doc.setTextColor(...textColor);
            let filterText = 'Applied Filters: ';
            const filters = [];
            if (purposeFilter) filters.push(`Purpose: ${purposeFilter}`);
            if (labFilter) filters.push(`Lab: ${labFilter}`);
            if (searchValue) filters.push(`Search: ${searchValue}`);
            if (selectedYear) filters.push(`Year: ${selectedYear}`);
            
            doc.text(filterText + filters.join(' | '), pageCenter, 82, { align: 'center' });
        }
        
        // Set font size for table content
        doc.setFontSize(9);
        
        const table = document.querySelector('table');
        const visibleRows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
        
        // Define column widths and positions with modern spacing
        const colWidths = [25, 50, 50, 25, 30, 25, 25];
        let yPos = 90; // Adjusted starting position for table
        
        // Calculate total width of the table
        const totalWidth = colWidths.reduce((a, b) => a + b, 0);
        // Calculate starting X position to center the table
        let startX = (pageWidth - totalWidth) / 2;
        
        // Draw headers with modern styling
        const headers = Array.from(table.querySelectorAll('th'));
        let xPos = startX;
        headers.forEach((header, index) => {
            // Header background
            doc.setFillColor(...secondaryColor);
            doc.rect(xPos, yPos, colWidths[index], 10, 'F');
            
            // Header text
            doc.setTextColor(255, 255, 255);
            doc.text(header.innerText, xPos + 2, yPos + 7);
            xPos += colWidths[index];
        });
        
        yPos += 12;
        
        // Draw filtered rows with modern styling
        visibleRows.forEach((row, rowIndex) => {
            const cells = Array.from(row.querySelectorAll('td'));
            xPos = startX;
            
            // Add subtle background for even rows
            if (rowIndex % 2 === 0) {
                doc.setFillColor(...lightGray);
                doc.rect(startX, yPos, totalWidth, 10, 'F');
            }
            
            // Cell content
            doc.setTextColor(...textColor);
            cells.forEach((cell, index) => {
                doc.text(cell.innerText, xPos + 2, yPos + 7);
                xPos += colWidths[index];
            });
            
            yPos += 10;
            
            // Add new page if content exceeds page height
            if (yPos >= 190) {
                doc.addPage();
                yPos = 20;
                
                // Redraw headers on new page
                xPos = startX;
                headers.forEach((header, index) => {
                    doc.setFillColor(...secondaryColor);
                    doc.setTextColor(255, 255, 255);
                    doc.rect(xPos, yPos, colWidths[index], 10, 'F');
                    doc.text(header.innerText, xPos + 2, yPos + 7);
                    xPos += colWidths[index];
                });
                yPos += 12;
            }
        });
        
        // Add modern footer
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(...textColor);
            
            // Footer background
            doc.setFillColor(...lightGray);
            doc.rect(0, doc.internal.pageSize.height - 15, pageWidth, 15, 'F');
            
            // Footer text
            doc.text(`Page ${i} of ${pageCount}`, pageWidth - 20, doc.internal.pageSize.height - 8);
            doc.text('© University of Cebu - Main', 20, doc.internal.pageSize.height - 8);
        }
        
        // Save the PDF
        doc.save('sitin_filtered_records.pdf');
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('There was an error generating the PDF. Please try again.');
    }
}
</script>
</body>
</html>
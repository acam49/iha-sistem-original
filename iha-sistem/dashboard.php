<?php
// filepath: dashboard.php
// Korumalı Dashboard Sayfası

require_once __DIR__ . '/api/auth.php';

$auth = new SHAAuth();

// Session kontrolü
if(!$auth->checkSession()) {
    header('Location: index.php?error=session');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İHA Sistem - Kontrol Paneli</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #fff;
        }
        .navbar {
            background: rgba(0,0,0,0.8);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #00ced1;
        }
        .navbar h2 {
            color: #00ced1;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .user-info span {
            color: #888;
        }
        .logout-btn {
            padding: 8px 20px;
            background: #ff4444;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .logout-btn:hover {
            background: #ff6666;
        }
        .container {
            padding: 30px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(0,206,209,0.3);
            border-radius: 15px;
            padding: 25px;
        }
        .card h3 {
            color: #00ced1;
            margin-bottom: 15px;
        }
        .card p {
            color: #aaa;
            line-height: 1.6;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            background: #00ced1;
            color: #000;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>İHA KONTROL SİSTEMİ</h2>
        <div class="user-info">
            <span>Kullanıcı: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
            <span class="status">GÜVENLİ BAĞLANTI</span>
            <a href="logout.php" class="logout-btn">Çıkış</a>
        </div>
    </div>
    
    <div class="container">
        <h1>Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p style="color: #888; margin-top: 10px;">SHA-256 şifreleme ile güvence altına alınmış oturumunuz aktif.</p>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>📡 İHA Fleet Yönetimi</h3>
                <p id="activeUavCount">Aktif İHA sayısı: 0</p>
                <div id="uavList" style="margin-top: 15px;"></div>
            </div>
            
            <div class="card">
                <h3>➕ Yeni İHA Ekle</h3>
                <form id="addUavForm" style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                    <input type="text" id="uavName" placeholder="İHA Adı (Örn: Bayraktar TB2)" required style="padding:8px;">
                    <input type="text" id="uavModel" placeholder="Model (Örn: Sabit Kanat)" required style="padding:8px;">
                    <input type="text" id="uavSerial" placeholder="Seri No" required style="padding:8px;">
                    <select id="uavStatus" style="padding:8px;">
                        <option value="Müsait">Müsait</option>
                        <option value="Uçuşta">Uçuşta</option>
                        <option value="Bakımda">Bakımda</option>
                        <option value="Arızalı">Arızalı</option>
                    </select>
                    <button type="submit" style="padding:8px; background:#00ced1; border:none; cursor:pointer;">Ekle</button>
                </form>
            </div>
            
            <div class="card">
                <h3>🗓️ Uçuş/Görev Planla</h3>
                <form id="addFlightForm" style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                    <select id="uav_id" required style="padding:8px;">
                        <option value="">-- İHA Seç --</option>
                    </select>
                    <input type="datetime-local" id="flightDate" required style="padding:8px;">
                    <div id="uavAvailabilityMsg" style="font-size:12px; min-height:15px;"></div>
                    <textarea id="flightNotes" placeholder="Görev Notları..." style="padding:8px;"></textarea>
                    <button type="submit" style="padding:8px; background:#00ced1; border:none; cursor:pointer;">Planla</button>
                </form>
            </div>
        </div>
    </div>
    <script src="js/dashboard_ajax.js"></script>
    <script src="js/tests.js"></script>
</body>
</html>
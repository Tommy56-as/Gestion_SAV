<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphique des Ventes Annuelles</title>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Reset et base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .header h1 {
            font-size: 2.8rem;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 800;
        }
        
        .header-subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        .header-badge {
            display: inline-flex;
            align-items: center;
            background: #e8f4ff;
            color: #3498db;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .header-badge i {
            margin-right: 8px;
        }
        
        /* Cartes statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border-left: 5px solid #3498db;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.green {
            border-left-color: #2ecc71;
        }
        
        .stat-card.purple {
            border-left-color: #9b59b6;
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .stat-title {
            font-size: 0.95rem;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .stat-card.blue .stat-icon {
            background: #e8f4ff;
            color: #3498db;
        }
        
        .stat-card.green .stat-icon {
            background: #e8f8f0;
            color: #2ecc71;
        }
        
        .stat-card.purple .stat-icon {
            background: #f4e8ff;
            color: #9b59b6;
        }
        
        .stat-trend {
            display: inline-flex;
            align-items: center;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 15px;
        }
        
        .trend-up {
            background: #d5f4e6;
            color: #27ae60;
        }
        
        .trend-down {
            background: #ffeaea;
            color: #e74c3c;
        }
        
        /* Section graphique */
        .chart-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .section-subtitle {
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .legend {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .legend-color.blue {
            background: linear-gradient(90deg, #3498db, #2980b9);
        }
        
        .legend-color.green {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            border: 2px dashed #27ae60;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        
        /* Analyse rapide */
        .quick-analysis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .analysis-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .analysis-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        
        .analysis-icon.blue {
            background: #e8f4ff;
            color: #3498db;
        }
        
        .analysis-icon.green {
            background: #e8f8f0;
            color: #2ecc71;
        }
        
        .analysis-icon.purple {
            background: #f4e8ff;
            color: #9b59b6;
        }
        
        /* Tableau */
        .table-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .total-badge {
            background: #f8fafc;
            color: #2c3e50;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #eef2f7;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .data-table thead {
            background: #f8fafc;
        }
        
        .data-table th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #eef2f7;
        }
        
        .data-table td {
            padding: 18px 20px;
            border-bottom: 1px solid #eef2f7;
            color: #4a5568;
        }
        
        .data-table tbody tr:hover {
            background: #f8fafc;
        }
        
        .month-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .month-number {
            width: 40px;
            height: 40px;
            background: #e8f4ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3498db;
            font-weight: 700;
        }
        
        .month-info {
            display: flex;
            flex-direction: column;
        }
        
        .month-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .month-period {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .sales-amount {
            font-weight: 700;
            font-size: 1.2rem;
            color: #2c3e50;
        }
        
        .sales-detail {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .progress-bar {
            width: 150px;
            height: 10px;
            background: #edf2f7;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2980b9);
            border-radius: 5px;
        }
        
        .trend-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .performance-stars {
            display: flex;
            gap: 4px;
        }
        
        .star {
            color: #ffd700;
        }
        
        .star.empty {
            color: #e0e0e0;
        }
        
        .indicator {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .indicator-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
        }
        
        .indicator-dot.green {
            background: #2ecc71;
        }
        
        .indicator-dot.yellow {
            background: #f1c40f;
        }
        
        .indicator-dot.red {
            background: #e74c3c;
        }
        
        .indicator-dot.blue {
            background: #3498db;
        }
        
        /* Résumé annuel */
        .annual-summary {
            background: linear-gradient(135deg, #e8f4ff 0%, #f0f7ff 100%);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }
        
        .summary-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .summary-value.green {
            color: #2ecc71;
        }
        
        .summary-value.purple {
            color: #9b59b6;
        }
        
        .summary-value.yellow {
            color: #f1c40f;
        }
        
        .summary-label {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        /* Bouton d'export */
        .export-section {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 40px;
        }
        
        .export-button {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
        }
        
        .export-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
        }
        
        .export-button i {
            margin-right: 10px;
        }
        
        .version-info {
            color: #95a5a6;
            font-size: 0.9rem;
            margin-top: 15px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.2rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .chart-container {
                height: 300px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .data-table th,
            .data-table td {
                padding: 12px 15px;
            }
            
            .stat-value {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-analysis {
                grid-template-columns: 1fr;
            }
            
            .legend {
                flex-direction: column;
                gap: 10px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header animate-fadeIn">
            <h1>📊 Tableau de Bord des Ventes 2024</h1>
            <p class="header-subtitle">Analyse détaillée des performances commerciales mensuelles</p>
            <div class="header-badge">
                <i class="fas fa-calendar-alt"></i>
                <span>Données mises à jour : Décembre 2024</span>
            </div>
        </header>
        
        <!-- Statistiques principales -->
        <div class="stats-grid">
            <!-- Carte 1 -->
            <div class="stat-card blue animate-fadeIn">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Ventes totales 2024</div>
                        <div class="stat-value">45,8M FCFA</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    18.5%
                </div>
                <p class="stat-info">vs 2023 (38,6M FCFA)</p>
            </div>
            
            <!-- Carte 2 -->
            <div class="stat-card green animate-fadeIn">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Meilleur mois</div>
                        <div class="stat-value">Décembre</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <p class="stat-info">6,2M FCFA - Record de ventes</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 85%"></div>
                </div>
            </div>
            
            <!-- Carte 3 -->
            <div class="stat-card purple animate-fadeIn">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Moyenne mensuelle</div>
                        <div class="stat-value">3,82M FCFA</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
                <p class="stat-info">8 mois sur 12 au-dessus de la moyenne</p>
            </div>
        </div>
        
        <!-- Section Graphique -->
        <section class="chart-section animate-fadeIn">
            <div class="section-header">
                <div>
                    <h2 class="section-title">📈 Évolution des ventes mensuelles 2024</h2>
                    <p class="section-subtitle">Visualisez la progression mensuelle avec indicateurs de performance</p>
                </div>
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color blue"></div>
                        <span>Ventes réelles</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color green"></div>
                        <span>Objectif (4M/mois)</span>
                    </div>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="ventesChart"></canvas>
            </div>
            
            <!-- Analyse rapide -->
            <div class="quick-analysis">
                <div class="analysis-card">
                    <div class="analysis-icon blue">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <p class="analysis-title">Période forte</p>
                        <p class="analysis-value">Novembre - Décembre</p>
                    </div>
                </div>
                
                <div class="analysis-card">
                    <div class="analysis-icon green">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div>
                        <p class="analysis-title">Objectifs atteints</p>
                        <p class="analysis-value">7 mois</p>
                    </div>
                </div>
                
                <div class="analysis-card">
                    <div class="analysis-icon purple">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="analysis-title">Tendance générale</p>
                        <p class="analysis-value">Croissante</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Tableau détaillé -->
        <section class="table-section animate-fadeIn">
            <div class="table-header">
                <h2 class="table-title">📋 Détail mensuel des ventes</h2>
                <div class="total-badge">Total : 45.800.000 FCFA</div>
            </div>
            
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th>Ventes (FCFA)</th>
                            <th>% du total</th>
                            <th>Progression</th>
                            <th>Performance</th>
                            <th>Indicateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Janvier -->
                        <tr>
                            <td>
                                <div class="month-cell">
                                    <div class="month-number">01</div>
                                    <div class="month-info">
                                        <div class="month-name">Janvier</div>
                                        <div class="month-period">Début d'année</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="sales-amount">3,2M</div>
                                <div class="sales-detail">3.200.000 FCFA</div>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 70%"></div>
                                </div>
                                <span class="percentage">7,0%</span>
                            </td>
                            <td>
                                <div class="trend-badge trend-down">
                                    <i class="fas fa-minus"></i>
                                    5%
                                </div>
                            </td>
                            <td>
                                <div class="performance-stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="far fa-star star empty"></i>
                                    <i class="far fa-star star empty"></i>
                                </div>
                            </td>
                            <td>
                                <div class="indicator">
                                    <div class="indicator-dot yellow">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <span>Moyenne</span>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Février -->
                        <tr>
                            <td>
                                <div class="month-cell">
                                    <div class="month-number" style="background: #ffeaea; color: #e74c3c;">02</div>
                                    <div class="month-info">
                                        <div class="month-name">Février</div>
                                        <div class="month-period">28 jours</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="sales-amount">2,8M</div>
                                <div class="sales-detail">2.800.000 FCFA</div>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 61%; background: #e74c3c;"></div>
                                </div>
                                <span class="percentage">6,1%</span>
                            </td>
                            <td>
                                <div class="trend-badge trend-down">
                                    <i class="fas fa-arrow-down"></i>
                                    12%
                                </div>
                            </td>
                            <td>
                                <div class="performance-stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="far fa-star star empty"></i>
                                    <i class="far fa-star star empty"></i>
                                    <i class="far fa-star star empty"></i>
                                </div>
                            </td>
                            <td>
                                <div class="indicator">
                                    <div class="indicator-dot red">
                                        <i class="fas fa-exclamation"></i>
                                    </div>
                                    <span>Faible</span>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Mars -->
                        <tr>
                            <td>
                                <div class="month-cell">
                                    <div class="month-number" style="background: #e8f8f0; color: #2ecc71;">03</div>
                                    <div class="month-info">
                                        <div class="month-name">Mars</div>
                                        <div class="month-period">Rétablissement</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="sales-amount">3,5M</div>
                                <div class="sales-detail">3.500.000 FCFA</div>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 76%; background: #2ecc71;"></div>
                                </div>
                                <span class="percentage">7,6%</span>
                            </td>
                            <td>
                                <div class="trend-badge trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    25%
                                </div>
                            </td>
                            <td>
                                <div class="performance-stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="far fa-star star empty"></i>
                                </div>
                            </td>
                            <td>
                                <div class="indicator">
                                    <div class="indicator-dot green">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <span>Bon</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Résumé annuel -->
            <div class="annual-summary">
                <h3 class="summary-title">🎯 Résumé de l'année 2024</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value">8</div>
                        <div class="summary-label">Mois positifs</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value green">+18.5%</div>
                        <div class="summary-label">Croissance vs 2023</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value purple">92%</div>
                        <div class="summary-label">Taux de réalisation</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value yellow">7</div>
                        <div class="summary-label">Records battus</div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Bouton d'export -->
        <div class="export-section">
            <button class="export-button" onclick="exportToPDF()">
                <i class="fas fa-download"></i> Exporter le rapport PDF
            </button>
            <p class="version-info">Version 1.0 • Mise à jour automatique</p>
        </div>
    </div>
    
    <script>
        // Données statiques pour 2024 (en millions)
        const ventes2024 = [3.2, 2.8, 3.5, 3.8, 4.1, 3.9, 3.6, 4.2, 4.5, 4.8, 5.3, 6.2];
        const objectifMensuel = 4.0;
        const moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        const objectifsData = Array(12).fill(objectifMensuel);
        
        // Initialiser le graphique
        const ctx = document.getElementById('ventesChart').getContext('2d');
        
        const ventesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: moisLabels,
                datasets: [
                    {
                        label: 'Ventes 2024 (en millions FCFA)',
                        data: ventes2024,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3498db',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8
                    },
                    {
                        label: 'Objectif mensuel (4M)',
                        data: objectifsData,
                        borderColor: '#2ecc71',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: "'Segoe UI', sans-serif"
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        titleFont: {
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 14
                        },
                        padding: 12,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label = label.split('(')[0] + ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(1) + 'M FCFA';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return value.toFixed(1) + 'M';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Montant en millions FCFA',
                            color: '#666',
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        
        // Fonction d'export PDF
        function exportToPDF() {
            const totalAnnuel = ventes2024.reduce((a, b) => a + b, 0);
            alert('Fonctionnalité d\'export PDF en cours de développement!\n\n' +
                  'Rapport généré :\n' +
                  '• Total annuel : ' + totalAnnuel.toFixed(1) + 'M FCFA\n' +
                  '• Meilleur mois : Décembre (6.2M)\n' +
                  '• Croissance : +18.5%');
        }
        
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.animate-fadeIn');
            elements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
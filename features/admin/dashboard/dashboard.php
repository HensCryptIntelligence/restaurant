<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>-</title>
    <style>
        :root {
            --color-bg-primary: #1a1a1a;
            --color-bg-secondary: #2d2d2d;
            --color-bg-card: #252525;
            --color-accent-green: #4ade80;
            --color-accent-pink: #f9a8d4;
            --color-accent-red: #e70000;
            --color-text-primary: #ffffff;
            --color-text-secondary: #d9d9d9;
            --color-text-muted: #adadad;
            --color-border: #3d4142;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            line-height: 1.6;
        

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 10px 30px 30px 30px;
        }

        header {
            margin-bottom: 40px;
        }

        .welcome-text {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--color-accent-pink);
        }

        .subtitle {
            color: var(--color-text-muted);
            font-size: 0.95rem;
            width: 80%;
        }

        /* Summary Sections */
        .summary-section {
            margin-bottom: 50px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .section-header svg {
            color: var(--color-accent-pink);
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        /* Summary Cards */
        .summary-card {
            background-color: var(--color-bg-card);
            border-radius: 12px;
            padding: 24px;
            position: relative;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            font-weight: 500;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-icon.sales {
            background-color: rgba(249, 168, 212, 0.15);
            color: var(--color-accent-pink);
        }

        .card-icon.transactions {
            background-color: rgba(249, 168, 212, 0.15);
            color: var(--color-accent-pink);
        }

        .card-icon.reservations {
            background-color: rgba(251, 207, 232, 0.15);
            color: #fbcfe8;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--color-text-primary);
        }

        .card-date {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 16px;
        }

        /* Chart Bars */
        .chart-bars {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 40px;
        }

        .bar {
            flex: 1;
            background-color: var(--color-accent-pink);
            border-radius: 2px 2px 0 0;
            min-height: 8px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .bar:hover {
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 80px 20px 30px 20px;
            }

            .welcome-text {
                font-size: 1.5rem;
            }

            .subtitle {
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .welcome-text {
                font-size: 1.3rem;
            }

            .section-header h2 {
                font-size: 1.2rem;
            }

            .subtitle {
                width: 100%;
            }

            .card-value {
                font-size: 1.6rem;
            }
        }

        @media (min-width: 1025px) {
            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus Styles */
        *:focus {
            outline: 2px solid var(--color-accent-green);
            outline-offset: 2px;
        }

        /* Accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <main class="main-content" id="main-content">
            <header>
                <h1 class="welcome-text">Welcome, Admin</h1>
                <p class="subtitle">Your dashboard is ready with the latest sales insights, transaction updates, and reservation activity. Review today's performance, explore recent data, and manage system operations with ease.</p>
            </header>

            <!-- Daily Summary Section -->
            <section class="summary-section">
                <div class="section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                        <title>Daily Summary Icon</title>
                        <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>
                    </svg>
                    <h2>Daily Summary</h2>
                </div>

                <div class="summary-grid">
                    <article class="summary-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Daily Sales</div>
                            </div>
                            <div class="card-icon sales">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                                    <title>Sales Icon</title>
                                    <circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="card-value">IDR 3150K</div>
                        <div class="card-date">30 February 2024</div>
                        <div class="chart-bars" aria-label="Daily sales chart">
                            <div class="bar" style="height: 45%"></div>
                            <div class="bar" style="height: 60%"></div>
                            <div class="bar" style="height: 75%"></div>
                            <div class="bar" style="height: 85%"></div>
                            <div class="bar" style="height: 95%"></div>
                            <div class="bar" style="height: 100%"></div>
                            <div class="bar" style="height: 90%"></div>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Daily Transactions</div>
                            </div>
                            <div class="card-icon transactions">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                                    <title>Transactions Icon</title>
                                    <path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="card-value">5 Transactions</div>
                        <div class="card-date">30 February 2024</div>
                        <div class="chart-bars" aria-label="Daily transactions chart">
                            <div class="bar" style="height: 50%"></div>
                            <div class="bar" style="height: 65%"></div>
                            <div class="bar" style="height: 55%"></div>
                            <div class="bar" style="height: 70%"></div>
                            <div class="bar" style="height: 85%"></div>
                            <div class="bar" style="height: 95%"></div>
                            <div class="bar" style="height: 100%"></div>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Daily Reservation</div>
                            </div>
                            <div class="card-icon reservations">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                                    <title>Reservations Icon</title>
                                    <path d="M16 14v2.2l1.6 1"/><path d="M16 2v4"/><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M3 10h5"/><path d="M8 2v4"/><circle cx="16" cy="16" r="6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="card-value">25 Seats</div>
                        <div class="card-date">30 February 2024</div>
                        <div class="chart-bars" aria-label="Daily reservation chart">
                            <div class="bar" style="height: 55%"></div>
                            <div class="bar" style="height: 70%"></div>
                            <div class="bar" style="height: 80%"></div>
                            <div class="bar" style="height: 75%"></div>
                            <div class="bar" style="height: 90%"></div>
                            <div class="bar" style="height: 85%"></div>
                            <div class="bar" style="height: 100%"></div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Monthly Summary Section -->
            <section class="summary-section">
                <div class="section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                        <title>Monthly Summary Icon</title>
                        <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>
                    </svg>
                    <h2>Monthly Summary</h2>
                </div>

                <div class="summary-grid">
                    <article class="summary-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Monthly Sales</div>
                            </div>
                            <div class="card-icon sales">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                                    <title>Sales Icon</title>
                                    <circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="card-value">IDR 3150000K</div>
                        <div class="card-date">1 Feb - 1 Mar 2025</div>
                        <div class="chart-bars" aria-label="Monthly sales chart">
                            <div class="bar" style="height: 40%"></div>
                            <div class="bar" style="height: 55%"></div>
                            <div class="bar" style="height: 65%"></div>
                            <div class="bar" style="height: 75%"></div>
                            <div class="bar" style="height: 85%"></div>
                            <div class="bar" style="height: 95%"></div>
                            <div class="bar" style="height: 100%"></div>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Monthly Transactions</div>
                            </div>
                            <div class="card-icon transactions">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                                    <title>Transactions Icon</title>
                                    <path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="card-value">120 Transactions</div>
                        <div class="card-date">1 Feb - 1 Mar 2025</div>
                        <div class="chart-bars" aria-label="Monthly transactions chart">
                            <div class="bar" style="height: 45%"></div>
                            <div class="bar" style="height: 60%"></div>
                            <div class="bar" style="height: 70%"></div>
                            <div class="bar" style="height: 80%"></div>
                            <div class="bar" style="height: 75%"></div>
                            <div class="bar" style="height: 90%"></div>
                            <div class="bar" style="height: 100%"></div>
                        </div>
                    </article>

                    <article class="summary-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Monthly Reservation</div>
                            </div>
                            <div class="card-icon reservations">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true">
                                    <title>Reservations Icon</title>
                                    <path d="M16 14v2.2l1.6 1"/><path d="M16 2v4"/><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M3 10h5"/><path d="M8 2v4"/><circle cx="16" cy="16" r="6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="card-value">750 Seats</div>
                        <div class="card-date">1 Feb - 1 Mar 2025</div>
                        <div class="chart-bars" aria-label="Monthly reservation chart">
                            <div class="bar" style="height: 50%"></div>
                            <div class="bar" style="height: 65%"></div>
                            <div class="bar" style="height: 75%"></div>
                            <div class="bar" style="height: 70%"></div>
                            <div class="bar" style="height: 85%"></div>
                            <div class="bar" style="height: 95%"></div>
                            <div class="bar" style="height: 100%"></div>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </div>

    
</body>
</html>
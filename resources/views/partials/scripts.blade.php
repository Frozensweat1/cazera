<script src="{{ asset('assets/js/custom.js') }}"></script>
<script defer="" src="{{ asset('assets/js/apexcharts.js') }}"></script>

<script>
    document.addEventListener('alpine:init', () => {
        // main section
        Alpine.data('scrollToTop', () => ({
            showTopButton: false,
            init() {
                window.onscroll = () => {
                    this.scrollFunction();
                };
            },

            scrollFunction() {
                if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                    this.showTopButton = true;
                } else {
                    this.showTopButton = false;
                }
            },

            goToTop() {
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
            },
        }));

        // theme customization
        Alpine.data('customizer', () => ({
            showCustomizer: false,
        }));

        // sidebar section
        Alpine.data('sidebar', () => ({
            init() {
                const selector = document.querySelector('.sidebar ul a[href="' + window.location
                    .pathname + '"]');
                if (selector) {
                    selector.classList.add('active');
                    const ul = selector.closest('ul.sub-menu');
                    if (ul) {
                        let ele = ul.closest('li.menu').querySelectorAll('.nav-link');
                        if (ele) {
                            ele = ele[0];
                            setTimeout(() => {
                                ele.click();
                            });
                        }
                    }
                }
            },
        }));

        // header section
        Alpine.data('header', () => ({
            init() {
                const selector = document.querySelector('ul.horizontal-menu a[href="' + window
                    .location.pathname + '"]');
                if (selector) {
                    selector.classList.add('active');
                    const ul = selector.closest('ul.sub-menu');
                    if (ul) {
                        let ele = ul.closest('li.menu').querySelectorAll('.nav-link');
                        if (ele) {
                            ele = ele[0];
                            setTimeout(() => {
                                ele.classList.add('active');
                            });
                        }
                    }
                }
            },

            notifications: {!! json_encode(
                auth()->user()?->isSuperAdmin()
                    ? collect()
                    ->merge(
                        \Illuminate\Support\Facades\Schema::hasTable('activity_logs')
                            ? \App\Models\ActivityLog::query()
                                ->with('user')
                                ->when(auth()->check() && ! auth()->user()->isSuperAdmin(), function ($query) {
                                    $branchIds = auth()->user()->accessibleBranches()->pluck('branches.id');

                                    $query->where(function ($query) use ($branchIds) {
                                        $query->where('user_id', auth()->id())
                                            ->orWhereIn('branch_id', $branchIds);
                                    });
                                })
                                ->latest('logged_at')
                                ->limit(10)
                                ->get()
                                ->map(fn ($log) => [
                                    'id' => 'activity-' . $log->id,
                                    'type' => 'Activity',
                                    'tone' => 'primary',
                                    'title' => $log->event ? str($log->event)->headline()->toString() : 'Activity logged',
                                    'message' => e($log->description ?: ($log->route_name ?: $log->url ?: 'Backoffice activity')),
                                    'actor' => e($log->user?->name ?? 'System'),
                                    'time' => $log->logged_at?->diffForHumans() ?? $log->created_at?->diffForHumans(),
                                    'sort_time' => optional($log->logged_at ?? $log->created_at)->timestamp,
                                ])
                            : collect()
                    )
                    ->merge(
                        \Illuminate\Support\Facades\Schema::hasTable('audit_logs')
                            ? \App\Models\AuditLog::query()
                                ->with('user')
                                ->when(auth()->check() && ! auth()->user()->isSuperAdmin(), function ($query) {
                                    $branchIds = auth()->user()->accessibleBranches()->pluck('branches.id');

                                    $query->where(function ($query) use ($branchIds) {
                                        $query->where('user_id', auth()->id())
                                            ->orWhereIn('branch_id', $branchIds);
                                    });
                                })
                                ->latest('logged_at')
                                ->limit(10)
                                ->get()
                                ->map(fn ($log) => [
                                    'id' => 'audit-' . $log->id,
                                    'type' => 'Audit',
                                    'tone' => 'success',
                                    'title' => $log->event ? str($log->event)->headline()->toString() : 'Record changed',
                                    'message' => e(trim(($log->auditable_label ?: class_basename($log->auditable_type ?? 'Record')) . ' updated')),
                                    'actor' => e($log->user?->name ?? 'System'),
                                    'time' => $log->logged_at?->diffForHumans() ?? $log->created_at?->diffForHumans(),
                                    'sort_time' => optional($log->logged_at ?? $log->created_at)->timestamp,
                                ])
                            : collect()
                    )
                    ->sortByDesc('sort_time')
                    ->take(10)
                    ->values()
                    : collect()
            ) !!},

            messages: {!! json_encode(
                \Illuminate\Support\Facades\Schema::hasTable('contact_messages')
                    ? \App\Models\ContactMessage::query()
                    ->accessible()
                    ->orderByDesc('received_at')
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get(['id','name','subject','message','status','received_at','created_at'])
                    ->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'name' => $m->name,
                            'subject' => $m->subject,
                            'message' => $m->message,
                            'status' => $m->status,
                            'time' => ($m->received_at ?? $m->created_at)?->diffForHumans(),
                        ];
                    })
                    ->values()
                    : collect()
            ) !!},


            languages: [{
                    id: 1,
                    key: 'Khmer',
                    value: 'kh',
                },
                {
                    id: 2,
                    key: 'Danish',
                    value: 'da',
                },
                {
                    id: 3,
                    key: 'English',
                    value: 'en',
                },
                {
                    id: 4,
                    key: 'French',
                    value: 'fr',
                },
                {
                    id: 5,
                    key: 'German',
                    value: 'de',
                },
                {
                    id: 6,
                    key: 'Greek',
                    value: 'el',
                },
                {
                    id: 7,
                    key: 'Hungarian',
                    value: 'hu',
                },
                {
                    id: 8,
                    key: 'Italian',
                    value: 'it',
                },
                {
                    id: 9,
                    key: 'Japanese',
                    value: 'ja',
                },
                {
                    id: 10,
                    key: 'Polish',
                    value: 'pl',
                },
                {
                    id: 11,
                    key: 'Portuguese',
                    value: 'pt',
                },
                {
                    id: 12,
                    key: 'Russian',
                    value: 'ru',
                },
                {
                    id: 13,
                    key: 'Spanish',
                    value: 'es',
                },
                {
                    id: 14,
                    key: 'Swedish',
                    value: 'sv',
                },
                {
                    id: 15,
                    key: 'Turkish',
                    value: 'tr',
                },
                {
                    id: 16,
                    key: 'Arabic',
                    value: 'ae',
                },
            ],

            removeNotification(value) {
                this.notifications = this.notifications.filter((d) => d.id !== value);
            },

            removeMessage(value) {
                this.messages = this.messages.filter((d) => d.id !== value);
            },
        }));

        // content section
        Alpine.data('sales', () => ({
            init() {
                isDark = this.$store.app.theme === 'dark' || this.$store.app.isDarkMode ? true :
                    false;
                isRtl = this.$store.app.rtlClass === 'rtl' ? true : false;

                const revenueChart = null;
                const salesByCategory = null;
                const dailySales = null;
                const totalOrders = null;

                // revenue
                setTimeout(() => {
                    this.revenueChart = new ApexCharts(this.$refs.revenueChart, this
                        .revenueChartOptions);
                    this.$refs.revenueChart.innerHTML = '';
                    this.revenueChart.render();

                    // sales by category
                    this.salesByCategory = new ApexCharts(this.$refs.salesByCategory, this
                        .salesByCategoryOptions);
                    this.$refs.salesByCategory.innerHTML = '';
                    this.salesByCategory.render();

                    // daily sales
                    this.dailySales = new ApexCharts(this.$refs.dailySales, this
                        .dailySalesOptions);
                    this.$refs.dailySales.innerHTML = '';
                    this.dailySales.render();

                    // total orders
                    this.totalOrders = new ApexCharts(this.$refs.totalOrders, this
                        .totalOrdersOptions);
                    this.$refs.totalOrders.innerHTML = '';
                    this.totalOrders.render();
                }, 300);

                this.$watch('$store.app.theme', () => {
                    isDark = this.$store.app.theme === 'dark' || this.$store.app
                        .isDarkMode ? true : false;

                    this.revenueChart.updateOptions(this.revenueChartOptions);
                    this.salesByCategory.updateOptions(this.salesByCategoryOptions);
                    this.dailySales.updateOptions(this.dailySalesOptions);
                    this.totalOrders.updateOptions(this.totalOrdersOptions);
                });

                this.$watch('$store.app.rtlClass', () => {
                    isRtl = this.$store.app.rtlClass === 'rtl' ? true : false;
                    this.revenueChart.updateOptions(this.revenueChartOptions);
                });
            },

            // revenue
            get revenueChartOptions() {
                return {
                    series: [{
                            name: 'Income',
                            data: [16800, 16800, 15500, 17800, 15500, 17000, 19000, 16000,
                                15000, 17000, 14000, 17000
                            ],
                        },
                        {
                            name: 'Expenses',
                            data: [16500, 17500, 16200, 17300, 16000, 19500, 16000, 17000,
                                16000, 19000, 18000, 19000
                            ],
                        },
                    ],
                    chart: {
                        height: 325,
                        type: 'area',
                        fontFamily: 'Nunito, sans-serif',
                        zoom: {
                            enabled: false,
                        },
                        toolbar: {
                            show: false,
                        },
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        show: true,
                        curve: 'smooth',
                        width: 2,
                        lineCap: 'square',
                    },
                    dropShadow: {
                        enabled: true,
                        opacity: 0.2,
                        blur: 10,
                        left: -7,
                        top: 22,
                    },
                    colors: isDark ? ['#2196f3', '#e7515a'] : ['#1b55e2', '#e7515a'],
                    markers: {
                        discrete: [{
                                seriesIndex: 0,
                                dataPointIndex: 6,
                                fillColor: '#1b55e2',
                                strokeColor: 'transparent',
                                size: 7,
                            },
                            {
                                seriesIndex: 1,
                                dataPointIndex: 5,
                                fillColor: '#e7515a',
                                strokeColor: 'transparent',
                                size: 7,
                            },
                        ],
                    },
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
                        'Oct', 'Nov', 'Dec'
                    ],
                    xaxis: {
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        },
                        crosshairs: {
                            show: true,
                        },
                        labels: {
                            offsetX: isRtl ? 2 : 0,
                            offsetY: 5,
                            style: {
                                fontSize: '12px',
                                cssClass: 'apexcharts-xaxis-title',
                            },
                        },
                    },
                    yaxis: {
                        tickAmount: 7,
                        labels: {
                            formatter: (value) => {
                                return value / 1000 + 'K';
                            },
                            offsetX: isRtl ? -30 : -10,
                            offsetY: 0,
                            style: {
                                fontSize: '12px',
                                cssClass: 'apexcharts-yaxis-title',
                            },
                        },
                        opposite: isRtl ? true : false,
                    },
                    grid: {
                        borderColor: isDark ? '#191e3a' : '#e0e6ed',
                        strokeDashArray: 5,
                        xaxis: {
                            lines: {
                                show: true,
                            },
                        },
                        yaxis: {
                            lines: {
                                show: false,
                            },
                        },
                        padding: {
                            top: 0,
                            right: 0,
                            bottom: 0,
                            left: 0,
                        },
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '16px',
                        markers: {
                            width: 10,
                            height: 10,
                            offsetX: -2,
                        },
                        itemMargin: {
                            horizontal: 10,
                            vertical: 5,
                        },
                    },
                    tooltip: {
                        marker: {
                            show: true,
                        },
                        x: {
                            show: false,
                        },
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            inverseColors: !1,
                            opacityFrom: isDark ? 0.19 : 0.28,
                            opacityTo: 0.05,
                            stops: isDark ? [100, 100] : [45, 100],
                        },
                    },
                };
            },

            // sales by category
            get salesByCategoryOptions() {
                return {
                    series: [985, 737, 270],
                    chart: {
                        type: 'donut',
                        height: 460,
                        fontFamily: 'Nunito, sans-serif',
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        show: true,
                        width: 25,
                        colors: isDark ? '#0e1726' : '#fff',
                    },
                    colors: isDark ? ['#5c1ac3', '#e2a03f', '#e7515a', '#e2a03f'] : ['#e2a03f',
                        '#5c1ac3', '#e7515a'
                    ],
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '14px',
                        markers: {
                            width: 10,
                            height: 10,
                            offsetX: -2,
                        },
                        height: 50,
                        offsetY: 20,
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                background: 'transparent',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '29px',
                                        offsetY: -10,
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '26px',
                                        color: isDark ? '#bfc9d4' : undefined,
                                        offsetY: 16,
                                        formatter: (val) => {
                                            return val;
                                        },
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        color: '#888ea8',
                                        fontSize: '29px',
                                        formatter: (w) => {
                                            return w.globals.seriesTotals.reduce(function(a,
                                                b) {
                                                return a + b;
                                            }, 0);
                                        },
                                    },
                                },
                            },
                        },
                    },
                    labels: ['Apparel', 'Sports', 'Others'],
                    states: {
                        hover: {
                            filter: {
                                type: 'none',
                                value: 0.15,
                            },
                        },
                        active: {
                            filter: {
                                type: 'none',
                                value: 0.15,
                            },
                        },
                    },
                };
            },

            // daily sales
            get dailySalesOptions() {
                return {
                    series: [{
                            name: 'Sales',
                            data: [44, 55, 41, 67, 22, 43, 21],
                        },
                        {
                            name: 'Last Week',
                            data: [13, 23, 20, 8, 13, 27, 33],
                        },
                    ],
                    chart: {
                        height: 160,
                        type: 'bar',
                        fontFamily: 'Nunito, sans-serif',
                        toolbar: {
                            show: false,
                        },
                        stacked: true,
                        stackType: '100%',
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        show: true,
                        width: 1,
                    },
                    colors: ['#e2a03f', '#e0e6ed'],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            legend: {
                                position: 'bottom',
                                offsetX: -10,
                                offsetY: 0,
                            },
                        },
                    }, ],
                    xaxis: {
                        labels: {
                            show: false,
                        },
                        categories: ['Sun', 'Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat'],
                    },
                    yaxis: {
                        show: false,
                    },
                    fill: {
                        opacity: 1,
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '25%',
                        },
                    },
                    legend: {
                        show: false,
                    },
                    grid: {
                        show: false,
                        xaxis: {
                            lines: {
                                show: false,
                            },
                        },
                        padding: {
                            top: 10,
                            right: -20,
                            bottom: -20,
                            left: -20,
                        },
                    },
                };
            },

            // total orders
            get totalOrdersOptions() {
                return {
                    series: [{
                        name: 'Sales',
                        data: [28, 40, 36, 52, 38, 60, 38, 52, 36, 40],
                    }, ],
                    chart: {
                        height: 290,
                        type: 'area',
                        fontFamily: 'Nunito, sans-serif',
                        sparkline: {
                            enabled: true,
                        },
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2,
                    },
                    colors: isDark ? ['#00ab55'] : ['#00ab55'],
                    labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                    yaxis: {
                        min: 0,
                        show: false,
                    },
                    grid: {
                        padding: {
                            top: 125,
                            right: 0,
                            bottom: 0,
                            left: 0,
                        },
                    },
                    fill: {
                        opacity: 1,
                        type: 'gradient',
                        gradient: {
                            type: 'vertical',
                            shadeIntensity: 1,
                            inverseColors: !1,
                            opacityFrom: 0.3,
                            opacityTo: 0.05,
                            stops: [100, 100],
                        },
                    },
                    tooltip: {
                        x: {
                            show: false,
                        },
                    },
                };
            },
        }));

        Alpine.data('homeDashboard', (config) => ({
            ...config,
            init() {
                isDark = this.$store.app.theme === 'dark' || this.$store.app.isDarkMode ? true :
                    false;

                setTimeout(() => {
                    this.salesTrendChart = new ApexCharts(this.$refs.salesTrendChart, this
                        .salesTrendOptions);
                    this.$refs.salesTrendChart.innerHTML = '';
                    this.salesTrendChart.render();

                    this.categoryChart = new ApexCharts(this.$refs.categoryChart, this
                        .categoryOptions);
                    this.$refs.categoryChart.innerHTML = '';
                    this.categoryChart.render();

                    this.paymentMixChart = new ApexCharts(this.$refs.paymentMixChart, this
                        .paymentMixOptions);
                    this.$refs.paymentMixChart.innerHTML = '';
                    this.paymentMixChart.render();

                    setTimeout(() => window.dispatchEvent(new Event('resize')), 150);
                }, 300);

                this.$watch('$store.app.theme', () => {
                    isDark = this.$store.app.theme === 'dark' || this.$store.app
                        .isDarkMode ? true : false;
                    this.salesTrendChart.updateOptions(this.salesTrendOptions);
                    this.categoryChart.updateOptions(this.categoryOptions);
                    this.paymentMixChart.updateOptions(this.paymentMixOptions);
                });
            },

            get salesTrendOptions() {
                return {
                    series: [{
                            name: 'Collected Sales',
                            type: 'area',
                            data: this.salesSeries,
                        },
                        {
                            name: 'Orders',
                            type: 'column',
                            data: this.ordersSeries,
                        },
                    ],
                    chart: {
                        height: 330,
                        type: 'line',
                        fontFamily: 'Nunito, sans-serif',
                        toolbar: {
                            show: false
                        },
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                        zoom: {
                            enabled: false
                        },
                    },
                    stroke: {
                        curve: 'smooth',
                        width: [3, 0],
                    },
                    dataLabels: {
                        enabled: false
                    },
                    colors: isDark ? ['#38bdf8', '#f59e0b'] : ['#2563eb', '#f97316'],
                    fill: {
                        type: ['gradient', 'solid'],
                        gradient: {
                            opacityFrom: 0.35,
                            opacityTo: 0.05
                        },
                    },
                    xaxis: {
                        categories: this.salesLabels,
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                    },
                    yaxis: [{
                            labels: {
                                style: {
                                    colors: isDark ? '#cbd5e1' : '#6b7280'
                                }
                            },
                        },
                        {
                            opposite: true,
                            labels: {
                                style: {
                                    colors: isDark ? '#cbd5e1' : '#6b7280'
                                }
                            },
                        },
                    ],
                    grid: {
                        borderColor: isDark ? '#1f2937' : '#e5e7eb',
                        strokeDashArray: 5
                    },
                };
            },

            get categoryOptions() {
                return {
                    series: this.categorySeries.length ? this.categorySeries : [1],
                    chart: {
                        type: 'donut',
                        height: 330,
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                    },
                    labels: this.categoryLabels.length ? this.categoryLabels : ['No data'],
                    colors: isDark ? ['#38bdf8', '#22c55e', '#f97316', '#a855f7', '#ec4899',
                        '#eab308'
                    ] : ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#eab308'],
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '62%'
                            }
                        }
                    },
                };
            },

            get paymentMixOptions() {
                return {
                    series: this.paymentSeries.length ? this.paymentSeries : [1],
                    chart: {
                        type: 'donut',
                        height: 330,
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                    },
                    labels: this.paymentLabels.length ? this.paymentLabels : ['No data'],
                    colors: isDark ? ['#22c55e', '#38bdf8', '#f97316', '#a855f7'] : ['#10b981',
                        '#2563eb', '#f59e0b', '#8b5cf6'
                    ],
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                };
            },
        }));

        Alpine.data('quantitativeDashboard', (config) => ({
            ...config,
            init() {
                isDark = this.$store.app.theme === 'dark' || this.$store.app.isDarkMode ? true :
                    false;
                isRtl = this.$store.app.rtlClass === 'rtl' ? true : false;

                setTimeout(() => {
                    this.weeklySalesChart = new ApexCharts(this.$refs.weeklySalesChart, this
                        .weeklySalesOptions);
                    this.$refs.weeklySalesChart.innerHTML = '';
                    this.weeklySalesChart.render();

                    setTimeout(() => window.dispatchEvent(new Event('resize')), 150);
                }, 300);

                this.$watch('$store.app.theme', () => {
                    isDark = this.$store.app.theme === 'dark' || this.$store.app
                        .isDarkMode ? true : false;
                    this.weeklySalesChart.updateOptions(this.weeklySalesOptions);
                });
            },

            get weeklySalesOptions() {
                return {
                    series: [{
                            name: 'Sales',
                            type: 'area',
                            data: this.weeklySales,
                        },
                        {
                            name: 'Orders',
                            type: 'column',
                            data: this.weeklyOrders || [],
                        },
                    ],
                    chart: {
                        height: 320,
                        type: 'line',
                        fontFamily: 'Nunito, sans-serif',
                        toolbar: {
                            show: false
                        },
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                        zoom: {
                            enabled: false
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: [3, 0]
                    },
                    colors: isDark ? ['#22c55e', '#f97316'] : ['#0ea5e9', '#f59e0b'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.05,
                        },
                    },
                    xaxis: {
                        categories: this.weeklyLabels,
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        },
                    },
                    grid: {
                        borderColor: isDark ? '#1f2937' : '#e5e7eb',
                        strokeDashArray: 5,
                    },
                };
            },
        }));

        Alpine.data('financialDashboard', (config) => ({
            ...config,
            init() {
                isDark = this.$store.app.theme === 'dark' || this.$store.app.isDarkMode ? true :
                    false;
                isRtl = this.$store.app.rtlClass === 'rtl' ? true : false;

                setTimeout(() => {
                    this.revenueExpenseChart = new ApexCharts(this.$refs
                        .revenueExpenseChart, this.revenueExpenseOptions);
                    this.$refs.revenueExpenseChart.innerHTML = '';
                    this.revenueExpenseChart.render();

                    this.paymentBreakdownChart = new ApexCharts(this.$refs
                        .paymentBreakdownChart, this.paymentBreakdownOptions);
                    this.$refs.paymentBreakdownChart.innerHTML = '';
                    this.paymentBreakdownChart.render();

                    setTimeout(() => window.dispatchEvent(new Event('resize')), 150);
                }, 300);

                this.$watch('$store.app.theme', () => {
                    isDark = this.$store.app.theme === 'dark' || this.$store.app
                        .isDarkMode ? true : false;
                    this.revenueExpenseChart.updateOptions(this.revenueExpenseOptions);
                    this.paymentBreakdownChart.updateOptions(this.paymentBreakdownOptions);
                });
            },

            get revenueExpenseOptions() {
                return {
                    series: [{
                            name: 'Revenue',
                            data: this.monthlyRevenue
                        },
                        {
                            name: 'Expenses',
                            data: this.monthlyExpenses
                        },
                    ],
                    chart: {
                        type: 'area',
                        height: 320,
                        toolbar: {
                            show: false
                        },
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                        zoom: {
                            enabled: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: isDark ? ['#22c55e', '#f97316'] : ['#2563eb', '#ef4444'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.35,
                            opacityTo: 0.05
                        }
                    },
                    xaxis: {
                        categories: this.monthlyLabels,
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        }
                    },
                    grid: {
                        borderColor: isDark ? '#1f2937' : '#e5e7eb',
                        strokeDashArray: 5
                    },
                };
            },

            get paymentBreakdownOptions() {
                return {
                    series: this.paymentBreakdown.length ? this.paymentBreakdown : [1],
                    chart: {
                        type: 'donut',
                        height: 360,
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                    },
                    labels: this.paymentLabels.length ? this.paymentLabels : ['No data'],
                    colors: isDark ? ['#22c55e', '#2563eb', '#f97316'] : ['#10b981', '#3b82f6',
                        '#f59e0b'
                    ],
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true
                                }
                            },
                        },
                    },
                };
            },
        }));

        Alpine.data('analyticalDashboard', (config) => ({
            ...config,
            init() {
                isDark = this.$store.app.theme === 'dark' || this.$store.app.isDarkMode ? true :
                    false;
                isRtl = this.$store.app.rtlClass === 'rtl' ? true : false;

                setTimeout(() => {
                    this.productionCostChart = new ApexCharts(this.$refs
                        .productionCostChart, this.productionCostOptions);
                    this.$refs.productionCostChart.innerHTML = '';
                    this.productionCostChart.render();

                    this.expenseCategoryChart = new ApexCharts(this.$refs
                        .expenseCategoryChart, this.expenseCategoryOptions);
                    this.$refs.expenseCategoryChart.innerHTML = '';
                    this.expenseCategoryChart.render();

                    this.maintenanceStatusChart = new ApexCharts(this.$refs
                        .maintenanceStatusChart, this.maintenanceStatusOptions);
                    this.$refs.maintenanceStatusChart.innerHTML = '';
                    this.maintenanceStatusChart.render();

                    this.moduleRevenueChart = new ApexCharts(this.$refs
                        .moduleRevenueChart, this.moduleRevenueOptions);
                    this.$refs.moduleRevenueChart.innerHTML = '';
                    this.moduleRevenueChart.render();

                    setTimeout(() => window.dispatchEvent(new Event('resize')), 150);
                }, 300);

                this.$watch('$store.app.theme', () => {
                    isDark = this.$store.app.theme === 'dark' || this.$store.app
                        .isDarkMode ? true : false;
                    this.productionCostChart.updateOptions(this.productionCostOptions);
                    this.expenseCategoryChart.updateOptions(this.expenseCategoryOptions);
                    this.maintenanceStatusChart.updateOptions(this
                        .maintenanceStatusOptions);
                    this.moduleRevenueChart.updateOptions(this.moduleRevenueOptions);
                });
            },

            get productionCostOptions() {
                return {
                    series: [{
                        name: 'Production Cost',
                        data: this.productionCosts
                    }],
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: {
                            show: false
                        },
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                            columnWidth: '45%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    colors: [isDark ? '#38bdf8' : '#3b82f6'],
                    xaxis: {
                        categories: this.periodLabels,
                        labels: {
                            rotate: -45,
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        }
                    },
                    grid: {
                        borderColor: isDark ? '#1f2937' : '#e5e7eb',
                        strokeDashArray: 5
                    },
                };
            },

            get expenseCategoryOptions() {
                return {
                    series: this.expenseAmounts.length ? this.expenseAmounts : [1],
                    chart: {
                        type: 'donut',
                        height: 360,
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                    },
                    labels: this.expenseLabels.length ? this.expenseLabels : ['No data'],
                    colors: isDark ? ['#22c55e', '#3b82f6', '#f97316', '#a855f7', '#ec4899'] : [
                        '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'
                    ],
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%'
                            }
                        }
                    },
                };
            },

            get maintenanceStatusOptions() {
                return {
                    series: Object.values(this.maintenanceStatus).length ? Object.values(this.maintenanceStatus) : [1],
                    chart: {
                        type: 'pie',
                        height: 320,
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                    },
                    labels: Object.keys(this.maintenanceStatus).length ? Object.keys(this.maintenanceStatus) : ['No data'],
                    colors: isDark ? ['#22c55e', '#f97316', '#ef4444', '#3b82f6', '#8b5cf6'] : [
                        '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'
                    ],
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                };
            },

            get moduleRevenueOptions() {
                return {
                    series: [{
                        name: 'Revenue',
                        data: this.moduleRevenue || []
                    }],
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: {
                            show: false
                        },
                        redrawOnParentResize: true,
                        redrawOnWindowResize: true,
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                            horizontal: true,
                            barHeight: '55%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    colors: [isDark ? '#22c55e' : '#10b981'],
                    xaxis: {
                        categories: this.moduleLabels || [],
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: isDark ? '#cbd5e1' : '#6b7280'
                            }
                        }
                    },
                    grid: {
                        borderColor: isDark ? '#1f2937' : '#e5e7eb',
                        strokeDashArray: 5
                    },
                };
            },
        }));

        Alpine.data('tabs', (config) => ({
            activeTab: null,
            storageKey: config.storageKey ?? 'tabs',
            defaultTab: config.defaultTab ?? null,

            tabs: [],
            indicatorStyle: '',

            init() {
                this.tabs = [...this.$el.querySelectorAll('[data-tab]')].map(el => ({
                    key: el.dataset.tab,
                    el
                }))

                // restore memory
                const saved = localStorage.getItem(this.storageKey)

                this.activeTab = saved ||
                    this.defaultTab ||
                    this.tabs[0]?.key

                this.$nextTick(() => {
                    this.updateIndicator()
                })

                window.addEventListener('resize', () => this.updateIndicator())
            },

            setTab(tab) {
                this.activeTab = tab
                localStorage.setItem(this.storageKey, tab)
                this.updateIndicator()
            },

            updateIndicator() {
                const active = this.tabs.find(t => t.key === this.activeTab)
                if (!active) return

                const parentRect = this.$refs.tabList.getBoundingClientRect()
                const rect = active.el.getBoundingClientRect()

                this.indicatorStyle = `
                width: ${rect.width}px;
                transform: translateX(${rect.left - parentRect.left}px);
            `
            }
        }));

    });
</script>

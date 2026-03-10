<?php
/**
 * Genera el shortcode del dashboard de informes [euno_reports_dashboard]
 * Versión 4.7: Estandarización de espaciados y paddings profesionales.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Reports_Dashboard {
    public static function init() {
        add_action( 'init', function() {
            add_shortcode( 'euno_reports_dashboard', array( 'LMSEU_Reports_Dashboard', 'render_dashboard' ) );
        });
    }

    public static function render_dashboard( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div style="padding: 40px; text-align: center; font-family: sans-serif;">Debes iniciar sesión para ver esta página.</div>';
        }

        $is_editor = false;
        if ( isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) {
            $is_editor = true;
        }

        $user = wp_get_current_user();
        $allowed = array_intersect( ['administrator', 'group_leader', 'liderdegrupo'], (array) $user->roles );
        if ( empty( $allowed ) && ! current_user_can('manage_options') ) {
            return '<div style="padding: 40px; text-align: center; font-family: sans-serif; color: #ef4444; font-weight: bold; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 5px;">No tienes permisos para ver este panel de informes. Solo los administradores y líderes de grupo pueden acceder.</div>';
        }

        if ( $is_editor ) {
            return '<div style="padding: 60px; text-align: center; font-family: \'Plus Jakarta Sans\', sans-serif; border: 2px dashed #3b82f6; background: #fff; border-radius: 5px; color: #3b82f6;">
                <i class="fas fa-chart-bar" style="font-size: 30px; margin-bottom: 15px; display: block;"></i>
                <div style="font-weight: 800; font-size: 18px; text-transform: uppercase; letter-spacing: 1px;">EUNO BI: Modo Edición</div>
            </div>';
        }

        ob_start();
        ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
            .ms-reports { font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
            
            /* Tarjetas con Padding Estandarizado */
            .ms-card { 
                background: #fff; 
                border: 1px solid #f1f5f9; 
                border-radius: 5px; 
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
                transition: all 0.3s ease; 
                padding: 32px !important; /* Padding profesional generoso */
            }        
            .ms-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: #3b82f633; }

            .ms-select { width: 100%; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 10px 12px; font-size: 12px; font-weight: 600; color: #475569; outline: none; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em; }
            
            .btn-action { background: #3b82f6; color: #fff; font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 12px 20px; border-radius: 5px; transition: all 0.3s; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
            .btn-action:hover { background: #2563eb !important; transform: scale(1.02); }

            .status-badge { padding: 3px 8px; border-radius: 5px; font-size: 9px; font-weight: 800; text-transform: uppercase; }
            .status-completed { background: #d1fae5; color: #059669; }
            .status-progress { background: #dbeafe; color: #2563eb; }
            .status-notstarted { background: #f1f5f9; color: #64748b; }

            .user-master-row { cursor: pointer; transition: all 0.2s; border-bottom: 1px solid #f1f5f9; }
            .user-master-row:hover { background-color: #f8fafc; }
            .course-detail-row { display: none; background-color: #fcfdfe; }
            .course-detail-row.active { display: table-row; }
            .toggle-icon { transition: transform 0.3s; color: #94a3b8; }
            .user-master-row.active .toggle-icon { transform: rotate(180deg); color: #3b82f6; }

            /* Fondo sutil para la página completa para contrastar con el header blanco */
            body.page-id-informes { background-color: #f8fafc !important; }
            .page-id-informes #content, .page-id-informes .site-content { background-color: transparent !important; }

            .page-id-informes .entry-header, .page-id-informes .page_title, .page-id-informes .entry-title { display: none !important; }
            
            /* KPI Small Cards */
            .kpi-card { padding: 20px !important; }
        </style>

        <div class="ms-reports max-w-[1250px] mx-auto p-4 md:p-8 mb-20">
            <div class="flex flex-col lg:flex-row gap-8">

                <!-- Sidebar Filtros -->
                <aside class="w-full lg:w-1/4 shrink-0">
                    <div class="ms-card sticky top-24">
                        <div class="flex items-center space-x-3 mb-8 pb-6 border-b border-slate-100">
                            <div class="bg-blue-50 text-blue-600 p-2.5 rounded-[5px] shadow-sm"><i class="fas fa-filter"></i></div>
                            <h2 class="text-lg font-extrabold text-slate-800 tracking-tight uppercase">Filtros</h2>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Año Académico</label>
                                <select id="filterYear" class="ms-select"><option value="Todos">Todos los años</option></select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Mes de Inicio</label>
                                <select id="filterMonth" class="ms-select">
                                    <option value="Todos">Todos los meses</option>
                                    <option value="01">Enero</option><option value="02">Febrero</option><option value="03">Marzo</option>
                                    <option value="04">Abril</option><option value="05">Mayo</option><option value="06">Junio</option>
                                    <option value="07">Julio</option><option value="08">Agosto</option><option value="09">Septiembre</option>
                                    <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Grupo EUNO</label>
                                <select id="filterGroup" class="ms-select"><option value="Todos">Todos los grupos</option></select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Curso / Programa</label>
                                <select id="filterCourse" class="ms-select"><option value="Todos">Todos los cursos</option></select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-blue-500 uppercase tracking-widest block mb-2">Estado del Usuario</label>
                                <select id="filterStatus" class="ms-select border-blue-100 bg-blue-50/50">
                                    <option value="Todos">Todos los estados</option>
                                    <option value="Completados">Completados</option>
                                    <option value="En progreso">En progreso</option>
                                    <option value="No iniciados">No iniciados</option>
                                </select>
                            </div>
                        </div>

                        <button onclick="location.reload()" class="btn-action w-full mt-10 shadow-lg shadow-blue-500/20 bg-slate-800">
                            <i class="fas fa-sync-alt mr-2"></i> Sincronizar Datos
                        </button>
                    </div>
                </aside>

                <!-- Contenido Principal -->
                <main class="w-full lg:w-3/4 space-y-8">

                    <!-- Header Section con Padding -->
                    <header class="flex flex-col md:flex-row justify-between items-start md:items-center p-8 bg-white border border-slate-100 rounded-[5px] shadow-sm gap-6">
                        <div>
                            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tighter">Panel de <span class="text-blue-600">Informes</span></h1>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Inteligencia de Negocio EUNO</p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3 w-full md:w-auto">
                            <div class="relative w-full sm:w-64">
                                <input type="text" id="searchInput" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-[5px] text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all" placeholder="Buscar usuario..." autocomplete="off">
                                <i class="fas fa-search absolute right-4 top-1/2 transform -translate-y-1/2 text-slate-300"></i>
                                <div id="searchSuggestions" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-[5px] shadow-xl z-50 hidden max-h-60 overflow-y-auto"></div>
                            </div>
                            <button onclick="exportToCSV()" class="btn-action bg-blue-600 !text-white shadow-md hover:bg-blue-700 w-full sm:w-auto">
                                <i class="fas fa-file-csv mr-2 text-sm"></i> EXPORTAR CSV
                            </button>
                        </div>
                    </header>

                    <!-- KPI Cards Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="ms-card kpi-card text-center border-b-4 border-b-indigo-500">
                            <div class="text-slate-400 font-bold text-[9px] uppercase tracking-widest mb-1">Cursos</div>
                            <div id="kpiCourses" class="text-2xl font-extrabold text-slate-800">0</div>
                        </div>
                        <div class="ms-card kpi-card text-center border-b-4 border-b-blue-500">
                            <div class="text-slate-400 font-bold text-[9px] uppercase tracking-widest mb-1">Usuarios</div>
                            <div id="kpiUniqueUsers" class="text-2xl font-extrabold text-slate-800">0</div>
                        </div>
                        <div class="ms-card kpi-card text-center border-b-4 border-b-sky-500">
                            <div class="text-slate-400 font-bold text-[9px] uppercase tracking-widest mb-1">Accesos</div>
                            <div id="kpiLogins" class="text-2xl font-extrabold text-slate-800">0</div>
                        </div>
                        <div class="ms-card kpi-card text-center border-b-4 border-b-amber-500">
                            <div class="text-slate-400 font-bold text-[9px] uppercase tracking-widest mb-1">Grupos</div>
                            <div id="kpiTotalInsc" class="text-2xl font-extrabold text-slate-800">0</div>
                        </div>
                        <div class="ms-card kpi-card text-center border-b-4 border-b-emerald-500">
                            <div class="text-slate-400 font-bold text-[9px] uppercase tracking-widest mb-1">Completados</div>
                            <div id="kpiRate" class="text-2xl font-extrabold text-slate-800">0%</div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="ms-card">
                            <h3 class="text-lg font-bold mb-8 text-slate-800 flex items-center tracking-tight"><i class="fas fa-trophy mr-3 text-indigo-500"></i> Ranking Finalizaciones</h3>
                            <div class="relative h-64 w-full"><canvas id="courseCompletionChart"></canvas></div>
                        </div>
                        <div class="ms-card">
                            <h3 class="text-lg font-bold mb-8 text-slate-800 flex items-center tracking-tight"><i class="fas fa-chart-pie mr-3 text-blue-500"></i> Estado de los Cursos</h3>
                            <div class="relative h-64 w-full flex justify-center"><canvas id="pieChart"></canvas></div>
                        </div>
                    </div>

                    <!-- Evolución Temporal -->
                    <div class="ms-card">
                        <h3 class="text-xl font-extrabold text-slate-800 mb-8 flex items-center tracking-tight"><i class="fas fa-chart-line mr-3 text-indigo-500"></i> Evolución de Inscripciones</h3>
                        <div class="relative h-72 w-full"><canvas id="evolutionChart"></canvas></div>
                    </div>

                    <!-- COMPARADOR DE PERIODOS -->
                    <div class="ms-card border-t-4 border-purple-500">
                        <h3 class="text-2xl font-extrabold text-slate-800 flex items-center tracking-tight mb-8">
                            <i class="fas fa-balance-scale mr-4 text-purple-500"></i> Comparativa Estratégica
                        </h3>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-4 bg-slate-50 p-6 rounded-xl border border-slate-100 mb-10">
                            <!-- Periodo A -->
                            <div class="w-full sm:flex-1 flex flex-col space-y-2 bg-white px-6 py-4 rounded-lg border border-indigo-100 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-50 pb-2">
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Periodo A</span>
                                    <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                                </div>
                                <div class="flex items-center space-x-3 pt-1">
                                    <select id="compYearA" class="bg-transparent text-[13px] font-bold text-slate-700 outline-none cursor-pointer border-none p-0 focus:ring-0 flex-1"></select>
                                    <span class="text-slate-200 font-bold">|</span>
                                    <select id="compMonthA" class="bg-transparent text-[13px] font-semibold text-slate-500 outline-none cursor-pointer border-none p-0 focus:ring-0 flex-1">
                                        <option value="Todos">Cualquier Mes</option>
                                        <option value="01">Enero</option><option value="02">Febrero</option><option value="03">Marzo</option>
                                        <option value="04">Abril</option><option value="05">Mayo</option><option value="06">Junio</option>
                                        <option value="07">Julio</option><option value="08">Agosto</option><option value="09">Septiembre</option>
                                        <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-slate-300 font-black text-[10px] uppercase italic px-2">VS</div>

                            <!-- Periodo B -->
                            <div class="w-full sm:flex-1 flex flex-col space-y-2 bg-white px-6 py-4 rounded-lg border border-slate-200 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-50 pb-2">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Periodo B</span>
                                    <div class="w-2 h-2 bg-slate-200 rounded-full"></div>
                                </div>
                                <div class="flex items-center space-x-3 pt-1">
                                    <select id="compYearB" class="bg-transparent text-[13px] font-bold text-slate-700 outline-none cursor-pointer border-none p-0 focus:ring-0 flex-1"></select>
                                    <span class="text-slate-200 font-bold">|</span>
                                    <select id="compMonthB" class="bg-transparent text-[13px] font-semibold text-slate-500 outline-none cursor-pointer border-none p-0 focus:ring-0 flex-1">
                                        <option value="Todos">Cualquier Mes</option>
                                        <option value="01">Enero</option><option value="02">Febrero</option><option value="03">Marzo</option>
                                        <option value="04">Abril</option><option value="05">Mayo</option><option value="06">Junio</option>
                                        <option value="07">Julio</option><option value="08">Agosto</option><option value="09">Septiembre</option>
                                        <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="relative h-96 w-full"><canvas id="comparisonChart"></canvas></div>
                    </div>

                    <!-- Tabla Agrupada (Acordeón) -->
                    <div class="ms-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Detalle Registros</h3>
                            <span class="bg-white border border-slate-200 px-4 py-1.5 rounded-full text-[10px] font-bold text-slate-500 shadow-sm uppercase" id="tableCount">0 REGISTROS</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead id="tableHead">
                                    <tr class="text-slate-400 text-[10px] uppercase font-black bg-white border-b border-slate-100">
                                        <th class="px-8 py-4 w-10"></th>
                                        <th class="px-8 py-4">Usuario</th>
                                        <th class="px-8 py-4">Grupo</th>
                                        <th class="px-8 py-4 text-center">Inscrip.</th>
                                        <th class="px-8 py-4 text-center">Progreso</th>
                                        <th class="px-8 py-4">Último Acceso</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody" class="divide-y divide-slate-50 text-sm">
                                    <tr><td colspan="6" class="text-center py-24 text-slate-400 font-bold uppercase text-[10px]">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script>
            let allData = [];
            let filteredData = [];
            let charts = {};

            async function init() {
                try {
                    const res = await fetch('/wp-json/wp-abilities/v1/abilities/learndash/get-user-course-report/run', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' },
                        body: JSON.stringify({ input: { limit: 10000, offset: 0 } })
                    });
                    const json = await res.json();
                    allData = json.value || json.result || json.data || (Array.isArray(json) ? json : []);
                    
                    populateFilters();
                    setupEventListeners();
                    updateDashboard();
                    updateComparisonChart();
                } catch (e) {
                    console.error("Dashboard Error:", e);
                    document.getElementById('tableBody').innerHTML = `<tr><td colspan="6" class="text-center py-12 text-red-500 font-bold uppercase">Error de Conexión</td></tr>`;
                }
            }

            function populateFilters() {
                const years = [...new Set(allData.map(d => d.anio))].filter(Boolean).sort().reverse();
                
                ['filterYear', 'compYearA', 'compYearB'].forEach(id => {
                    const el = document.getElementById(id);
                    years.forEach(y => { el.innerHTML += `<option value="${y}">${y}</option>`; });
                });

                const groupsSet = new Set();
                allData.forEach(d => { if(d.grupos_del_usuario) d.grupos_del_usuario.split(',').forEach(g => { if(g.trim()) groupsSet.add(g.trim()); }); });
                const selectGroup = document.getElementById('filterGroup');
                [...groupsSet].sort().forEach(g => { selectGroup.innerHTML += `<option value="${g}">${g}</option>`; });

                const coursesSet = new Set();
                allData.forEach(d => { if(d.titulo_del_curso) coursesSet.add(d.titulo_del_curso.trim()); });
                const selectCourse = document.getElementById('filterCourse');
                [...coursesSet].sort().forEach(c => { selectCourse.innerHTML += `<option value="${c}">${c}</option>`; });
            }

            function setupEventListeners() {
                const searchInput = document.getElementById('searchInput');
                const suggestionsBox = document.getElementById('searchSuggestions');

                ['filterYear', 'filterMonth', 'filterGroup', 'filterCourse', 'filterStatus'].forEach(id => {
                    const el = document.getElementById(id);
                    if(el) el.addEventListener('change', updateDashboard);
                });

                if(searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        const val = e.target.value.toLowerCase();
                        updateDashboard();

                        if(val.length < 2) {
                            suggestionsBox.classList.add('hidden');
                            return;
                        }

                        const matches = [...new Set(allData
                            .filter(d => d.nombre && d.nombre.toLowerCase().includes(val))
                            .map(d => d.nombre))]
                            .slice(0, 5);

                        if(matches.length > 0) {
                            suggestionsBox.innerHTML = matches.map(m => `
                                <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm text-slate-700 border-b border-slate-50 last:border-0 font-medium" onclick="selectSuggestion('${m}')">
                                    <i class="fas fa-user text-blue-300 mr-2 text-[10px]"></i> ${m}
                                </div>
                            `).join('');
                            suggestionsBox.classList.remove('hidden');
                        } else {
                            suggestionsBox.classList.add('hidden');
                        }
                    });
                }

                document.addEventListener('click', (e) => {
                    if(!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                        suggestionsBox.classList.add('hidden');
                    }
                });

                ['compYearA', 'compMonthA', 'compYearB', 'compMonthB'].forEach(id => {
                    const el = document.getElementById(id);
                    if(el) el.addEventListener('change', updateComparisonChart);
                });
            }

            window.selectSuggestion = function(name) {
                const searchInput = document.getElementById('searchInput');
                const suggestionsBox = document.getElementById('searchSuggestions');
                searchInput.value = name;
                suggestionsBox.classList.add('hidden');
                updateDashboard();
            };

            function determineStatus(d) {
                const s = (d.curso_completado || '').toString().toLowerCase().trim();
                return (s === 'yes' || s === '1' || s === 'true' || s === 'completado') ? 'Completados' : (parseInt(d.pasos_completados || 0) === 0 ? 'No iniciados' : 'En progreso');    
            }

            function updateDashboard() {
                const year = document.getElementById('filterYear').value;
                const month = document.getElementById('filterMonth').value;
                const group = document.getElementById('filterGroup').value;
                const course = document.getElementById('filterCourse').value;
                const status = document.getElementById('filterStatus').value;
                const search = document.getElementById('searchInput').value.toLowerCase();

                filteredData = allData.filter(d => {
                    const matchYear = year === 'Todos' || d.anio === year;
                    const matchMonth = month === 'Todos' || d.mes === month;
                    const matchGroup = group === 'Todos' || (d.grupos_del_usuario && d.grupos_del_usuario.includes(group));
                    const matchCourse = course === 'Todos' || d.titulo_del_curso === course;
                    const matchStatus = status === 'Todos' || determineStatus(d) === status;
                    const matchSearch = !search || (d.nombre && d.nombre.toLowerCase().includes(search)) || (d.titulo_del_curso && d.titulo_del_curso.toLowerCase().includes(search));
                    return matchYear && matchMonth && matchGroup && matchCourse && matchStatus && matchSearch;
                });

                renderKPIs(filteredData);
                renderMainCharts(filteredData);
                renderTable(filteredData);
            }

            function renderKPIs(data) {
                const coursesCount = new Set(data.map(d => d.titulo_del_curso)).size;
                const uniqueUsersCount = new Set(data.map(d => d.id_de_usuario)).size;
                const totalInsc = data.length;
                const logins = data.reduce((acc, curr) => acc + (parseInt(curr.total_logins) || 0), 0);
                const completed = data.filter(d => determineStatus(d) === 'Completados').length;
                const rate = totalInsc > 0 ? ((completed / totalInsc) * 100).toFixed(1) : 0;

                document.getElementById('kpiCourses').innerText = coursesCount;
                document.getElementById('kpiUniqueUsers').innerText = uniqueUsersCount;
                document.getElementById('kpiLogins').innerText = logins;
                document.getElementById('kpiTotalInsc').innerText = totalInsc;
                document.getElementById('kpiRate').innerText = rate + '%';
            }

            function updateComparisonChart() {
                const yA = document.getElementById('compYearA').value;
                const mA = document.getElementById('compMonthA').value;
                const yB = document.getElementById('compYearB').value;
                const mB = document.getElementById('compMonthB').value;

                const dataA = allData.filter(d => (yA === 'Todos' || d.anio === yA) && (mA === 'Todos' || d.mes === mA));
                const dataB = allData.filter(d => (yB === 'Todos' || d.anio === yB) && (mB === 'Todos' || d.mes === mB));

                const metricsA = [dataA.length, dataA.filter(d => determineStatus(d) === 'Completados').length, new Set(dataA.map(d => d.id_de_usuario)).size];
                const metricsB = [dataB.length, dataB.filter(d => determineStatus(d) === 'Completados').length, new Set(dataB.map(d => d.id_de_usuario)).size];

                if(charts.comparison) charts.comparison.destroy();
                charts.comparison = new Chart(document.getElementById('comparisonChart'), {
                    type: 'bar',
                    data: {
                        labels: ['Inscripciones Totales', 'Finalizaciones', 'Usuarios Únicos'],
                        datasets: [
                            { label: `Periodo A`, data: metricsA, backgroundColor: '#6366f1', borderRadius: 5, barPercentage: 0.6 },
                            { label: `Periodo B`, data: metricsB, backgroundColor: '#cbd5e1', borderRadius: 5, barPercentage: 0.6 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { font: { weight: '800', size: 10 } } } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } } } }
                });
            }

            function renderMainCharts(data) {
                Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
                Chart.defaults.color = '#94a3b8';

                const timeMap = data.reduce((acc, curr) => {
                    const key = `${curr.anio}-${curr.mes.toString().padStart(2, '0')}`;
                    acc[key] = (acc[key] || 0) + 1;
                    return acc;
                }, {});
                const sortedLabels = Object.keys(timeMap).sort();

                if(charts.evolution) charts.evolution.destroy();
                charts.evolution = new Chart(document.getElementById('evolutionChart'), {
                    type: 'line',
                    data: { labels: sortedLabels, datasets: [{ label: 'Nuevas Inscripciones', data: sortedLabels.map(l => timeMap[l]), borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.05)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
                });

                const courseComp = data.reduce((acc, curr) => { if (determineStatus(curr) === 'Completados') acc[curr.titulo_del_curso] = (acc[curr.titulo_del_curso] || 0) + 1; return acc; }, {});
                const topCourses = Object.entries(courseComp).sort((a,b) => b[1] - a[1]).slice(0, 5);

                if(charts.completion) charts.completion.destroy();
                charts.completion = new Chart(document.getElementById('courseCompletionChart'), {
                    type: 'bar',
                    data: { labels: topCourses.map(c => c[0].substring(0,25)+'...'), datasets: [{ data: topCourses.map(c => c[1]), backgroundColor: '#6366f1', borderRadius: 5 }] },      
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }
                });

                const statusCounts = { 'Completados': 0, 'En progreso': 0, 'No iniciados': 0 };
                data.forEach(d => { statusCounts[determineStatus(d)]++; });

                if(charts.pie) charts.pie.destroy();
                charts.pie = new Chart(document.getElementById('pieChart'), {
                    type: 'doughnut',
                    data: { labels: ['Completados', 'En progreso', 'No iniciados'], datasets: [{ data: Object.values(statusCounts), backgroundColor: ['#4f46e5', '#94a3b8', '#e2e8f0'], borderWidth: 0, hoverOffset: 10 }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: 'bold', size: 10 } } } } }
                });
            }

            function renderTable(data) {
                const body = document.getElementById('tableBody');
                const grouped = data.reduce((acc, curr) => {
                    const uid = curr.id_de_usuario;
                    if (!acc[uid]) acc[uid] = { id: uid, nombre: curr.nombre, email: curr.email, grupo: curr.grupos_del_usuario, last: curr.last_login_date, courses: [] };
                    acc[uid].courses.push(curr); return acc;
                }, {});
                const users = Object.values(grouped).slice(0, 100);

                body.innerHTML = users.map(u => {
                    const totalSteps = u.courses.reduce((sum, c) => sum + (parseInt(c.pasos_totales) || 0), 0);
                    const compSteps = u.courses.reduce((sum, c) => sum + (parseInt(c.pasos_completados) || 0), 0);
                    const avg = totalSteps > 0 ? Math.round((compSteps / totalSteps) * 100) : 0;
                    
                    let detailRows = u.courses.map(c => {
                        const status = determineStatus(c);
                        let badge = status === 'Completados' ? 'status-completed' : (status === 'En progreso' ? 'status-progress' : 'status-notstarted');
                        return `
                            <div class="flex items-center justify-between py-3 px-8 border-b border-slate-50 last:border-0 hover:bg-slate-50/50">
                                <div class="flex-1"><div class="text-xs font-bold text-slate-700">${c.titulo_del_curso}</div></div>
                                <div class="w-32 text-center font-black text-slate-600 text-[11px]">${c.pasos_completados} / ${c.pasos_totales}</div>
                                <div class="w-32 text-center"><span class="status-badge ${badge}">${status}</span></div>
                                <div class="w-40 text-right text-[10px] text-slate-400 font-bold uppercase">${c.curso_completado_el || 'En curso...'}</div>
                            </div>
                        `;
                    }).join('');

                    return `
                        <tr class="user-master-row" onclick="toggleUserDetail('${u.id}', this)">
                            <td class="px-8 py-5 text-center"><i class="fas fa-chevron-down toggle-icon"></i></td>
                            <td class="px-8 py-5"><div class="font-bold text-slate-800 text-sm">${u.nombre}</div><div class="text-[9px] text-slate-400 font-bold">${u.email}</div></td>
                            <td class="px-8 py-5"><span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded-md font-bold border border-slate-200">${u.grupo || '-'}</span></td>
                            <td class="px-8 py-5 text-center font-black text-blue-600 text-sm">${u.courses.length}</td>
                            <td class="px-8 py-5"><div class="flex items-center space-x-3"><div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-blue-500" style="width: ${avg}%"></div></div><span class="text-[10px] font-black text-slate-600">${avg}%</span></div></td>
                            <td class="px-8 py-5 text-[10px] text-slate-400 font-black uppercase">${u.last || 'N/A'}</td>
                        </tr>
                        <tr id="detail-${u.id}" class="course-detail-row"><td colspan="6" class="p-0 border-b border-slate-100 shadow-inner"><div class="bg-slate-50/20">${detailRows}</div></td></tr>
                    `;
                }).join('');
                document.getElementById('tableCount').innerText = `${data.length} REGISTROS`;
            }

            function toggleUserDetail(uid, rowEl) {
                const detailRow = document.getElementById(`detail-${uid}`);
                const isActive = detailRow.classList.contains('active');
                if (isActive) { detailRow.classList.remove('active'); rowEl.classList.remove('active'); }
                else { detailRow.classList.add('active'); rowEl.classList.add('active'); }
            }

            function exportToCSV() {
                const headers = ["id_de_usuario", "nombre", "email", "id_del_curso", "titulo_del_curso", "pasos_completados", "pasos_totales", "curso_completado", "curso_completado_el", "total_time", "completion_time", "Username", "First Name", "Last Name", "Group(s)", "course_started_on", "course_total_time_on", "course_last_step_id", "course_last_step_type", "course_last_step_title", "last_login_date"];
                const rows = filteredData.map(d => [d.id_de_usuario, `"${d.nombre}"`, d.email, d.id_del_curso, `"${d.titulo_del_curso}"`, d.pasos_completados, d.pasos_totales, d.curso_completado, d.curso_completado_el, d.total_time || "00:00:00", d.completion_time || "", d.Username || "", `"${d['First Name'] || ''}"`, `"${d['Last Name'] || ''}"`, `"${d['Group(s)'] || ''}"`, d.course_started_on || "", d.course_total_time_on || "00:00:00", d.course_last_step_id || 0, d.course_last_step_type || "", `"${d.course_last_step_title || ''}"`, d.last_login_date || ""]);
                let csvContent = "\uFEFF" + headers.join(",") + "\n" + rows.map(r => r.join(",")).join("\n");
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.setAttribute("href", url);
                link.setAttribute("download", "Reporte_EUNO_BI.csv");
                document.body.appendChild(link); link.click(); document.body.removeChild(link);
            }

            document.addEventListener('DOMContentLoaded', init);
        </script>
        <?php
        return ob_get_clean();
    }
}
LMSEU_Reports_Dashboard::init();
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Intelligence Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .tab-active { border-bottom: 2px solid #2563eb; color: #2563eb; background-color: #eff6ff; }
        .tab-inactive { color: #64748b; }
        .tab-inactive:hover { background-color: #f1f5f9; }
        .animate-fade { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 fixed w-full z-20 top-0 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold"><i class="fa-solid fa-chart-pie"></i></div>
                    <span class="font-bold text-slate-700 text-lg">Data Intelligence</span>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="location.reload()" class="text-slate-400 hover:text-blue-600 transition p-2"><i class="fa-solid fa-rotate-right text-lg"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12">
        <div id="upload-card" class="bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center max-w-2xl mx-auto mt-10">
            <div class="mb-6">
                <div class="h-16 w-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <h2 class="text-2xl font-bold text-slate-800">Importar Dados</h2>
                <p class="text-slate-500 mt-2">Envie o arquivo .xlsx para processamento ETL.</p>
            </div>
            <div id="dropzone" class="border-2 border-dashed border-slate-300 rounded-xl p-8 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                <input type="file" id="fileInput" class="hidden" accept=".xlsx">
                <span class="text-slate-600 font-medium">Clique ou arraste o arquivo aqui</span>
            </div>
            <div id="loading" class="hidden mt-8 flex justify-center items-center gap-3">
                <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-xl"></i>
                <span class="text-slate-600 font-medium">Processando...</span>
            </div>
        </div>

        <div id="dashboard-area" class="hidden animate-fade">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-slate-800">Overview</h1>
                <div id="download-buttons" class="flex gap-2"></div>
            </div>

            <div class="bg-white rounded-t-xl border-b border-slate-200 px-4 flex gap-4 overflow-x-auto sticky top-16 z-10">
                <button onclick="switchTab('MonthView')" id="tab-MonthView" class="tab-active py-4 px-4 font-medium text-sm">Mensal (Std)</button>
                <button onclick="switchTab('SpecialView')" id="tab-SpecialView" class="tab-inactive py-4 px-4 font-medium text-sm">Mensal (Spc)</button>
                <button onclick="switchTab('CycleView')" id="tab-CycleView" class="tab-inactive py-4 px-4 font-medium text-sm">Ciclo (Std)</button>
            </div>

            <div class="bg-white rounded-b-xl shadow-sm border border-t-0 border-slate-200 p-6 min-h-[500px]">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="p-5 rounded-xl border border-slate-100 bg-slate-50"><p class="text-xs font-bold text-slate-400 uppercase">Total</p><p class="text-3xl font-bold text-slate-800" id="kpi-total">---</p></div>
                    <div class="p-5 rounded-xl border border-slate-100 bg-slate-50"><p class="text-xs font-bold text-slate-400 uppercase">Linhas</p><p class="text-3xl font-bold text-slate-800" id="kpi-count">---</p></div>
                    <div class="p-5 rounded-xl border border-slate-100 bg-slate-50"><p class="text-xs font-bold text-slate-400 uppercase">Supervisores</p><p class="text-3xl font-bold text-slate-800" id="kpi-supervisores">---</p></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 lg:sticky lg:top-24">
                        <div class="bg-white rounded-xl border border-slate-100 p-5">
                            <h3 class="font-bold text-slate-700 mb-4 text-sm">Distribuição</h3>
                            <div class="relative h-64 w-full"><canvas id="mainChart"></canvas></div>
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <div id="accordion-container" class="space-y-3 pb-10"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let appData = null, currentChart = null;
        const dropzone = document.getElementById('dropzone'), fileInput = document.getElementById('fileInput'), loading = document.getElementById('loading');
        
        dropzone.onclick = () => fileInput.click();
        fileInput.onchange = (e) => processFiles(e.target.files);
        dropzone.ondragover = (e) => { e.preventDefault(); dropzone.classList.add('border-blue-500', 'bg-blue-50'); };
        dropzone.ondragleave = () => dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        dropzone.ondrop = (e) => { e.preventDefault(); dropzone.classList.remove('border-blue-500', 'bg-blue-50'); processFiles(e.dataTransfer.files); };

        function processFiles(files) {
            if(!files.length) return;
            const formData = new FormData(); formData.append('file', files[0]);
            dropzone.classList.add('hidden'); loading.classList.remove('hidden');

            fetch('api.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(json => {
                    loading.classList.add('hidden');
                    if(json.error) return Swal.fire('Error', json.error, 'error').then(() => dropzone.classList.remove('hidden'));
                    appData = json.data;
                    initDashboard(json.download_path);
                    Swal.fire({ icon: 'success', title: 'Data Processed', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                })
                .catch(() => { loading.classList.add('hidden'); dropzone.classList.remove('hidden'); Swal.fire('Error', 'Connection failed', 'error'); });
        }

        function initDashboard(path) {
            document.getElementById('upload-card').classList.add('hidden');
            document.getElementById('dashboard-area').classList.remove('hidden');
            const btnArea = document.getElementById('download-buttons'); btnArea.innerHTML = '';
            appData.files_generated.forEach(f => {
                const a = document.createElement('a'); a.href = path + f; a.download = f; a.className = "bg-slate-800 text-white text-xs font-bold py-2 px-4 rounded-lg hover:bg-slate-900"; a.innerHTML = `<i class="fa-solid fa-download"></i> ${f}`; btnArea.appendChild(a);
            });
            switchTab('MonthView');
        }

        function switchTab(key) {
            ['MonthView', 'CycleView', 'SpecialView'].forEach(k => {
                document.getElementById('tab-'+k).className = (k === key) ? "tab-active py-4 px-4 font-medium text-sm" : "tab-inactive py-4 px-4 font-medium text-sm";
            });
            renderData(key);
        }

        function renderData(key) {
            const data = appData.datasets[key];
            const isMoney = data.type === 'Standard';
            const fmt = (v) => isMoney ? new Intl.NumberFormat('pt-BR', {style:'currency', currency:'BRL'}).format(v) : new Intl.NumberFormat('pt-BR').format(v);

            const grouped = {}, chartData = {};
            data.rows.forEach(r => {
                if(!grouped[r.supervisor_id]) grouped[r.supervisor_id] = {total:0, items:[]};
                grouped[r.supervisor_id].total += r.value;
                grouped[r.supervisor_id].items.push(r);
                chartData[r.product_line] = (chartData[r.product_line] || 0) + r.value;
            });

            document.getElementById('kpi-total').innerText = fmt(data.total);
            document.getElementById('kpi-count').innerText = data.rows.length;
            document.getElementById('kpi-supervisores').innerText = Object.keys(grouped).length;

            const container = document.getElementById('accordion-container'); container.innerHTML = '';
            Object.keys(grouped).forEach(sup => {
                const group = grouped[sup];
                const div = document.createElement('div');
                div.className = "border border-slate-200 rounded-lg bg-white overflow-hidden shadow-sm";
                div.innerHTML = `<div class="bg-slate-50 p-4 flex justify-between cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <span class="font-bold text-sm text-slate-700">Supervisor ${sup}</span>
                    <span class="font-bold text-sm">${fmt(group.total)}</span>
                </div>
                <div class="hidden border-t border-slate-100 p-4 text-xs text-slate-500">${group.items.length} records in this group.</div>`;
                container.appendChild(div);
            });
            renderChart(chartData);
        }

        function renderChart(data) {
            const ctx = document.getElementById('mainChart').getContext('2d');
            if(currentChart) currentChart.destroy();
            currentChart = new Chart(ctx, { type: 'doughnut', data: { labels: Object.keys(data), datasets: [{ data: Object.values(data), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'] }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } } } });
        }
    </script>
</body>
</html>
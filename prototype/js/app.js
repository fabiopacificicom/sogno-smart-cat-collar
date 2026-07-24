// Smart Cat Collar — Mock Data & Interactivity

const MOCK_CATS = [
  { id: 1, name: 'Antifa', breed: 'Domestic Shorthair', status: 'healthy', temperature: 38.4, bpm: 175, activity: 'high', lastReading: '1 min ago', avatar: '🐱', tempTrend: 'stable', bpmTrend: 'stable', actTrend: 'up' },
  { id: 2, name: 'Anakin', breed: 'Domestic Shorthair', status: 'healthy', temperature: 38.7, bpm: 190, activity: 'medium', lastReading: '3 min ago', avatar: '😺', tempTrend: 'stable', bpmTrend: 'down', actTrend: 'stable' },
  { id: 3, name: 'Mando', breed: 'Domestic Shorthair', status: 'warning', temperature: 39.1, bpm: 225, activity: 'low', lastReading: '2 min ago', avatar: '🐈', tempTrend: 'up', bpmTrend: 'up', actTrend: 'down' },
  { id: 4, name: 'Grogu', breed: 'Domestic Shorthair', status: 'critical', temperature: 39.8, bpm: 260, activity: 'low', lastReading: '1 min ago', avatar: '😺', tempTrend: 'up', bpmTrend: 'up', actTrend: 'down' },
  { id: 5, name: 'Gaza', breed: 'Domestic Shorthair', status: 'healthy', temperature: 38.2, bpm: 160, activity: 'high', lastReading: '4 min ago', avatar: '🐱', tempTrend: 'down', bpmTrend: 'stable', actTrend: 'up' },
  { id: 6, name: 'Jabba', breed: 'Domestic Shorthair', status: 'healthy', temperature: 38.9, bpm: 185, activity: 'medium', lastReading: '5 min ago', avatar: '🐈', tempTrend: 'stable', bpmTrend: 'stable', actTrend: 'stable' },
  { id: 7, name: 'Sabbia', breed: 'Domestic Shorthair', status: 'healthy', temperature: 38.6, bpm: 170, activity: 'medium', lastReading: '2 min ago', avatar: '😺', tempTrend: 'stable', bpmTrend: 'down', actTrend: 'up' }
];

const MOCK_ALERTS = [
  { id: 1, catId: 4, catName: 'Grogu', type: 'critical', message: 'Temperature 39.8°C exceeds critical threshold (39.5°C)', time: '1 min ago', vital: 'temperature', value: 39.8, threshold: 39.5 },
  { id: 2, catId: 3, catName: 'Mando', type: 'warning', message: 'BPM 225 exceeds warning threshold (220)', time: '5 min ago', vital: 'bpm', value: 225, threshold: 220 },
  { id: 3, catId: 4, catName: 'Grogu', type: 'critical', message: 'BPM 260 exceeds critical threshold (250)', time: '12 min ago', vital: 'bpm', value: 260, threshold: 250 },
  { id: 4, catId: 3, catName: 'Mando', type: 'warning', message: 'Temperature 39.1°C exceeds warning threshold (39.0°C)', time: '20 min ago', vital: 'temperature', value: 39.1, threshold: 39.0 },
  { id: 5, catId: 5, catName: 'Gaza', type: 'info', message: 'Activity level returned to normal', time: '1 hour ago', vital: 'activity', value: 'high', threshold: null },
];

const MOCK_READINGS = [];
const now = Date.now();
for (let i = 0; i < 20; i++) {
  MOCK_READINGS.push({
    time: new Date(now - i * 300000).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
    temperature: (38.2 + Math.random() * 1.8).toFixed(1),
    bpm: Math.floor(160 + Math.random() * 100),
    activity: ['low', 'medium', 'high'][Math.floor(Math.random() * 3)]
  });
}

// Status helpers
function statusColor(status) {
  return { healthy: 'text-teal-500', warning: 'text-amber-500', critical: 'text-red-500' }[status] || 'text-gray-500';
}
function statusBg(status) {
  return { healthy: 'bg-teal-50 border-teal-200', warning: 'bg-amber-50 border-amber-200', critical: 'bg-red-50 border-red-200' }[status] || 'bg-gray-50 border-gray-200';
}
function statusEmoji(status) {
  return { healthy: '🟢', warning: '🟡', critical: '🔴' }[status] || '⚪';
}
function trendIcon(trend) {
  return { up: '↑', stable: '→', down: '↓' }[trend] || '→';
}
function trendColor(trend) {
  return { up: 'text-red-500', stable: 'text-gray-400', down: 'text-teal-500' }[trend] || 'text-gray-400';
}

// Setup Wizard
function initSetup() {
  let currentStep = 1;
  const totalSteps = 5;

  function showStep(step) {
    document.querySelectorAll('[data-step]').forEach(el => el.classList.add('hidden'));
    const target = document.querySelector(`[data-step="${step}"]`);
    if (target) target.classList.remove('hidden');

    // Update progress
    const progress = document.getElementById('setup-progress');
    if (progress) progress.style.width = `${(step / totalSteps) * 100}%`;

    // Update step indicators
    for (let i = 1; i <= totalSteps; i++) {
      const dot = document.getElementById(`step-dot-${i}`);
      if (dot) {
        dot.className = i <= step
          ? 'w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center text-sm font-bold'
          : 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold';
      }
    }

    // Navigation buttons
    const prevBtn = document.getElementById('btn-prev');
    const nextBtn = document.getElementById('btn-next');
    if (prevBtn) prevBtn.classList.toggle('hidden', step === 1);
    if (nextBtn) {
      nextBtn.textContent = step === totalSteps ? 'Start Monitoring 🐱' : 'Next';
    }
    currentStep = step;
  }

  window.nextStep = function() {
    if (currentStep === totalSteps) {
      // Save setup
      const catName = document.getElementById('cat-name')?.value || 'My Cat';
      const catBreed = document.getElementById('cat-breed')?.value || 'Domestic Shorthair';
      const provider = document.querySelector('input[name="provider"]:checked')?.value || 'mock';
      localStorage.setItem('setup_completed', 'true');
      localStorage.setItem('cats', JSON.stringify([{ id: 1, name: catName, breed: catBreed }]));
      localStorage.setItem('provider', provider);
      window.location.href = 'dashboard.html';
      return;
    }
    showStep(currentStep + 1);
  };

  window.prevStep = function() {
    if (currentStep > 1) showStep(currentStep - 1);
  };

  window.useDefaults = function() {
    const tw = document.getElementById('temp-warning');
    const tc = document.getElementById('temp-critical');
    const bw = document.getElementById('bpm-warning');
    const bc = document.getElementById('bpm-critical');
    if (tw) tw.value = '39.0';
    if (tc) tc.value = '39.5';
    if (bw) bw.value = '220';
    if (bc) bc.value = '250';
  };

  showStep(1);
}

// Dashboard
function initDashboard() {
  const catGrid = document.getElementById('cat-grid');
  const alertLog = document.getElementById('alert-log');

  if (catGrid) {
    const cats = JSON.parse(localStorage.getItem('cats') || 'null') || MOCK_CATS;
    catGrid.innerHTML = cats.map(cat => `
      <a href="cat-detail.html?id=${cat.id}" class="block rounded-xl border-2 ${statusBg(cat.status)} p-5 hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-3">
            <span class="text-3xl">${cat.avatar || '🐱'}</span>
            <div>
              <h3 class="font-bold text-gray-800">${cat.name}</h3>
              <p class="text-xs text-gray-500">${cat.breed}</p>
            </div>
          </div>
          <span class="text-xl">${statusEmoji(cat.status)}</span>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-4">
          <div class="text-center">
            <p class="text-xs text-gray-500">🌡️ Temp</p>
            <p class="font-bold ${cat.temperature > 39.0 ? 'text-red-500' : 'text-gray-800'}">${cat.temperature}°C</p>
            <p class="text-xs ${trendColor(cat.tempTrend)}">${trendIcon(cat.tempTrend)}</p>
          </div>
          <div class="text-center">
            <p class="text-xs text-gray-500">❤️ BPM</p>
            <p class="font-bold ${cat.bpm > 220 ? 'text-red-500' : 'text-gray-800'}">${cat.bpm}</p>
            <p class="text-xs ${trendColor(cat.bpmTrend)}">${trendIcon(cat.bpmTrend)}</p>
          </div>
          <div class="text-center">
            <p class="text-xs text-gray-500">🏃 Activity</p>
            <p class="font-bold capitalize text-gray-800">${cat.activity}</p>
            <p class="text-xs ${trendColor(cat.actTrend)}">${trendIcon(cat.actTrend)}</p>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Last reading: ${cat.lastReading}</p>
      </a>
    `).join('');
  }

  if (alertLog) {
    alertLog.innerHTML = MOCK_ALERTS.map(alert => `
      <div class="flex items-start gap-3 py-3 ${alert.type === 'critical' ? 'bg-red-50' : alert.type === 'warning' ? 'bg-amber-50' : 'bg-gray-50'} px-4 rounded-lg mb-2">
        <span class="text-lg">${alert.type === 'critical' ? '🔴' : alert.type === 'warning' ? '🟡' : 'ℹ️'}</span>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-800">${alert.catName}: ${alert.message}</p>
          <p class="text-xs text-gray-400">${alert.time}</p>
        </div>
      </div>
    `).join('');
  }
}

// Cat Detail
function initCatDetail() {
  const params = new URLSearchParams(window.location.search);
  const catId = parseInt(params.get('id')) || 1;
  const cat = MOCK_CATS.find(c => c.id === catId) || MOCK_CATS[0];

  document.getElementById('cat-name-header').textContent = cat.name;
  document.getElementById('cat-avatar').textContent = cat.avatar;
  document.getElementById('cat-status').textContent = cat.status.charAt(0).toUpperCase() + cat.status.slice(1);

  document.getElementById('cat-temp').textContent = cat.temperature + '°C';
  document.getElementById('cat-bpm').textContent = cat.bpm;
  document.getElementById('cat-activity').textContent = cat.activity.charAt(0).toUpperCase() + cat.activity.slice(1);

  const readingsTable = document.getElementById('readings-table');
  if (readingsTable) {
    readingsTable.innerHTML = MOCK_READINGS.map(r => `
      <tr class="border-b border-gray-100">
        <td class="py-2 text-sm text-gray-600">${r.time}</td>
        <td class="py-2 text-sm ${parseFloat(r.temperature) > 39.0 ? 'text-red-500 font-bold' : 'text-gray-800'}">${r.temperature}°C</td>
        <td class="py-2 text-sm ${parseInt(r.bpm) > 220 ? 'text-red-500 font-bold' : 'text-gray-800'}">${r.bpm}</td>
        <td class="py-2 text-sm capitalize text-gray-800">${r.activity}</td>
        <td class="py-2 text-sm">${parseFloat(r.temperature) > 39.5 ? '🔴' : parseFloat(r.temperature) > 39.0 ? '🟡' : '🟢'}</td>
      </tr>
    `).join('');
  }

  const catAlerts = MOCK_ALERTS.filter(a => a.catId === catId);
  const alertHistory = document.getElementById('cat-alert-history');
  if (alertHistory) {
    alertHistory.innerHTML = catAlerts.length ? catAlerts.map(a => `
      <div class="flex items-start gap-3 py-2 border-b border-gray-100">
        <span>${a.type === 'critical' ? '🔴' : '🟡'}</span>
        <div>
          <p class="text-sm text-gray-800">${a.message}</p>
          <p class="text-xs text-gray-400">${a.time}</p>
        </div>
      </div>
    `).join('') : '<p class="text-sm text-gray-400 py-2">No alerts for this cat</p>';
  }
}

// Settings Tabs
function initSettings() {
  window.switchTab = function(tabName) {
    document.querySelectorAll('[data-tab]').forEach(el => el.classList.add('hidden'));
    document.querySelector(`[data-tab="${tabName}"]`)?.classList.remove('hidden');
    document.querySelectorAll('[data-tab-btn]').forEach(btn => {
      btn.className = btn.dataset.tabBtn === tabName
        ? 'px-4 py-2 text-sm font-medium text-orange-600 border-b-2 border-orange-500'
        : 'px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700';
    });
  };
}

// Route initializer
document.addEventListener('DOMContentLoaded', () => {
  const page = document.body.dataset.page;
  if (page === 'setup') initSetup();
  else if (page === 'dashboard') initDashboard();
  else if (page === 'cat-detail') initCatDetail();
  else if (page === 'settings') initSettings();
  else if (page === 'index') {
    if (localStorage.getItem('setup_completed')) {
      window.location.href = 'dashboard.html';
    } else {
      window.location.href = 'setup.html';
    }
  }
});

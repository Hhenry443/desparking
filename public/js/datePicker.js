(function () {
  const pad = (n) => String(n).padStart(2, "0");
  const MONTHS = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December",
  ];
  const DAYS = ["Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"];

  function fmtDate(d) {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }

  function friendlyLabel(dateStr) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    if (dateStr === fmtDate(today)) return "Today";
    if (dateStr === fmtDate(tomorrow)) return "Tomorrow";
    return new Date(dateStr + "T00:00:00").toLocaleDateString("en-GB", {
      weekday: "short", day: "numeric", month: "short",
    });
  }

  function makeDatePicker(triggerId, labelId, hiddenId) {
    const trigger = document.getElementById(triggerId);
    const label   = document.getElementById(labelId);
    const hidden  = document.getElementById(hiddenId);
    if (!trigger || !label || !hidden) return null;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const todayStr = fmtDate(today);

    // Build and attach calendar popup to <body>
    const calEl = document.createElement("div");
    calEl.className =
      "fixed z-[9999] hidden select-none bg-white rounded-2xl border border-gray-100 " +
      "shadow-[0_8px_32px_rgba(0,0,0,0.14)] p-4 w-72";
    calEl.innerHTML = `
      <div class="flex items-center justify-between mb-4">
        <button type="button" data-action="prev"
          class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
          <i class="fa-solid fa-chevron-left text-xs"></i>
        </button>
        <span data-role="title" class="text-sm font-bold text-[#060745]"></span>
        <button type="button" data-action="next"
          class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
          <i class="fa-solid fa-chevron-right text-xs"></i>
        </button>
      </div>
      <div class="grid grid-cols-7 mb-2">
        ${DAYS.map((d) => `<span class="text-center text-xs font-semibold text-gray-400 pb-1">${d}</span>`).join("")}
      </div>
      <div data-role="grid" class="grid grid-cols-7 gap-y-0.5"></div>
    `;
    document.body.appendChild(calEl);

    const titleEl = calEl.querySelector("[data-role='title']");
    const gridEl  = calEl.querySelector("[data-role='grid']");

    let viewYear, viewMonth, selectedStr;
    let isOpen = false;

    function position() {
      const r   = trigger.getBoundingClientRect();
      const calW = 288;
      let left = r.left;
      if (left + calW > window.innerWidth - 8) left = window.innerWidth - calW - 8;
      calEl.style.top  = r.bottom + 6 + "px";
      calEl.style.left = left + "px";
    }

    function render() {
      titleEl.textContent = `${MONTHS[viewMonth]} ${viewYear}`;
      const firstDow  = new Date(viewYear, viewMonth, 1).getDay();
      const offset    = firstDow === 0 ? 6 : firstDow - 1;
      const daysTotal = new Date(viewYear, viewMonth + 1, 0).getDate();

      let html = "";
      for (let i = 0; i < offset; i++) html += "<div></div>";
      for (let d = 1; d <= daysTotal; d++) {
        const ds         = `${viewYear}-${pad(viewMonth + 1)}-${pad(d)}`;
        const isPast     = ds < todayStr;
        const isToday    = ds === todayStr;
        const isSelected = ds === selectedStr;

        let cls =
          "h-8 w-8 mx-auto flex items-center justify-center rounded-full text-xs transition ";
        if (isPast) {
          cls += "text-gray-300 cursor-default";
        } else if (isSelected) {
          cls += "bg-[#6ae6fc] text-gray-900 font-bold cursor-pointer";
        } else if (isToday) {
          cls += "ring-2 ring-[#6ae6fc] text-gray-800 font-semibold hover:bg-[#6ae6fc]/20 cursor-pointer";
        } else {
          cls += "text-gray-700 hover:bg-gray-100 cursor-pointer font-medium";
        }

        html += isPast
          ? `<div class="${cls}">${d}</div>`
          : `<div class="${cls}" data-date="${ds}">${d}</div>`;
      }
      gridEl.innerHTML = html;
    }

    function open()  { isOpen = true;  position(); calEl.classList.remove("hidden"); render(); }
    function close() { isOpen = false; calEl.classList.add("hidden"); }

    function select(dateStr) {
      const d  = new Date(dateStr + "T00:00:00");
      viewYear  = d.getFullYear();
      viewMonth = d.getMonth();
      selectedStr      = dateStr;
      hidden.value     = dateStr;
      label.textContent = friendlyLabel(dateStr);
      close();
    }

    trigger.addEventListener("click", (e) => { e.stopPropagation(); isOpen ? close() : open(); });

    calEl.querySelector("[data-action='prev']").addEventListener("click", (e) => {
      e.stopPropagation();
      if (--viewMonth < 0) { viewMonth = 11; viewYear--; }
      render();
    });
    calEl.querySelector("[data-action='next']").addEventListener("click", (e) => {
      e.stopPropagation();
      if (++viewMonth > 11) { viewMonth = 0; viewYear++; }
      render();
    });
    gridEl.addEventListener("click", (e) => {
      const cell = e.target.closest("[data-date]");
      if (cell) select(cell.dataset.date);
    });

    window.addEventListener("resize", () => { if (isOpen) position(); });

    // Track for global close-on-outside-click
    window._allDatePickers = window._allDatePickers || [];
    window._allDatePickers.push({ close });

    return { select, get isOpen() { return isOpen; }, close };
  }

  // Close all pickers when clicking outside any of them
  document.addEventListener("click", () => {
    (window._allDatePickers || []).forEach((p) => p.close());
  });

  // Build a <select> with 30-min time slots
  function buildTimeSelect(selectId, selectedHour) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = "";
    for (let h = 0; h < 24; h++) {
      for (const m of [0, 30]) {
        const lbl = new Date(2000, 0, 1, h, m).toLocaleTimeString("en-GB", {
          hour: "2-digit", minute: "2-digit",
        });
        const opt = new Option(lbl, `${pad(h)}:${pad(m)}`);
        if (h === selectedHour && m === 0) opt.selected = true;
        sel.appendChild(opt);
      }
    }
  }

  window.makeDatePicker = makeDatePicker;
  window.buildTimeSelect = buildTimeSelect;
  window.friendlyDateLabel = friendlyLabel;
})();

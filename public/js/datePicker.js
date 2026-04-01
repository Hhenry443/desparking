(function () {
  const pad = (n) => String(n).padStart(2, "0");
  const MONTHS = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December",
  ];
  const DAYS = ["Mo","Tu","We","Th","Fr","Sa","Su"];

  const TIME_SLOTS = [];
  for (let h = 0; h < 24; h++) for (const m of [0, 30]) TIME_SLOTS.push(`${pad(h)}:${pad(m)}`);

  function fmtDate(d) {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }

  function timeLabel(val) {
    const [h, m] = val.split(":").map(Number);
    return `${h % 12 || 12}:${pad(m)}${h < 12 ? "am" : "pm"}`;
  }

  function friendlyLabel(dateStr) {
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);
    if (dateStr === fmtDate(today))    return "Today";
    if (dateStr === fmtDate(tomorrow)) return "Tomorrow";
    return new Date(dateStr + "T00:00:00").toLocaleDateString("en-GB", {
      weekday: "short", day: "numeric", month: "short",
    });
  }

  // ─── Date picker ────────────────────────────────────────────────────────────

  function makeDatePicker(triggerId, labelId, hiddenId, onChange, placement) {
    const trigger = document.getElementById(triggerId);
    const label   = document.getElementById(labelId);
    const hidden  = document.getElementById(hiddenId);
    if (!trigger || !label || !hidden) return null;

    const today = new Date(); today.setHours(0, 0, 0, 0);
    const todayStr = fmtDate(today);

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
    let viewYear, viewMonth, selectedStr, isOpen = false;

    function position() {
      const r = trigger.getBoundingClientRect();
      const calW = 288;
      let left = r.left;
      if (left + calW > window.innerWidth - 8) left = window.innerWidth - calW - 8;
      if (placement === "above") {
        calEl.style.top    = "auto";
        calEl.style.bottom = (window.innerHeight - r.top + 6) + "px";
      } else {
        calEl.style.bottom = "auto";
        calEl.style.top    = (r.bottom + 6) + "px";
      }
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
        const ds = `${viewYear}-${pad(viewMonth + 1)}-${pad(d)}`;
        const isPast = ds < todayStr, isToday = ds === todayStr, isSel = ds === selectedStr;
        let cls = "h-8 w-8 mx-auto flex items-center justify-center rounded-full text-xs transition ";
        if (isPast)       cls += "text-gray-300 cursor-default";
        else if (isSel)   cls += "bg-[#6ae6fc] text-gray-900 font-bold cursor-pointer";
        else if (isToday) cls += "ring-2 ring-[#6ae6fc] text-gray-800 font-semibold hover:bg-[#6ae6fc]/20 cursor-pointer";
        else              cls += "text-gray-700 hover:bg-gray-100 cursor-pointer font-medium";
        html += isPast ? `<div class="${cls}">${d}</div>`
                       : `<div class="${cls}" data-date="${ds}">${d}</div>`;
      }
      gridEl.innerHTML = html;
    }

    function open()  { isOpen = true;  position(); calEl.classList.remove("hidden"); render(); }
    function close() { isOpen = false; calEl.classList.add("hidden"); }

    function select(dateStr) {
      const d = new Date(dateStr + "T00:00:00");
      viewYear = d.getFullYear(); viewMonth = d.getMonth();
      selectedStr = dateStr; hidden.value = dateStr;
      label.textContent = friendlyLabel(dateStr);
      close();
      if (onChange) onChange(dateStr);
    }

    trigger.addEventListener("click", (e) => { e.stopPropagation(); isOpen ? close() : open(); });
    calEl.querySelector("[data-action='prev']").addEventListener("click", (e) => {
      e.stopPropagation(); if (--viewMonth < 0) { viewMonth = 11; viewYear--; } render();
    });
    calEl.querySelector("[data-action='next']").addEventListener("click", (e) => {
      e.stopPropagation(); if (++viewMonth > 11) { viewMonth = 0; viewYear++; } render();
    });
    gridEl.addEventListener("click", (e) => {
      const cell = e.target.closest("[data-date]"); if (cell) select(cell.dataset.date);
    });
    window.addEventListener("resize", () => { if (isOpen) position(); });

    window._allDatePickers = window._allDatePickers || [];
    window._allDatePickers.push({ close });

    return { select, close, get isOpen() { return isOpen; } };
  }

  // ─── Time picker ────────────────────────────────────────────────────────────

  function makeTimePicker(triggerId, labelId, hiddenId, onChange, placement) {
    const trigger = document.getElementById(triggerId);
    const label   = document.getElementById(labelId);
    const hidden  = document.getElementById(hiddenId);
    if (!trigger || !label || !hidden) return null;

    const listEl = document.createElement("ul");
    listEl.className =
      "fixed z-[9999] hidden bg-white rounded-xl border border-gray-200 " +
      "shadow-[0_8px_24px_rgba(0,0,0,0.12)] overflow-y-auto";
    listEl.style.cssText += "max-height:11rem;width:7rem;";

    TIME_SLOTS.forEach((val) => {
      const li = document.createElement("li");
      li.textContent    = timeLabel(val);
      li.dataset.value  = val;
      li.className      = "px-3 py-2 text-sm cursor-pointer hover:bg-[#6ae6fc]/20 transition";
      listEl.appendChild(li);
    });
    document.body.appendChild(listEl);

    let isOpen = false;

    function position() {
      const r = trigger.getBoundingClientRect();
      let left = r.left;
      if (left + 112 > window.innerWidth - 8) left = window.innerWidth - 112 - 8;
      if (placement === "above") {
        listEl.style.top    = "auto";
        listEl.style.bottom = (window.innerHeight - r.top + 4) + "px";
      } else {
        listEl.style.bottom = "auto";
        listEl.style.top    = (r.bottom + 4) + "px";
      }
      listEl.style.left = left + "px";
    }

    function open() {
      isOpen = true; position(); listEl.classList.remove("hidden");
      const active = listEl.querySelector(".dp-time-active");
      if (active) active.scrollIntoView({ block: "nearest" });
    }
    function close() { isOpen = false; listEl.classList.add("hidden"); }

    function setValue(val) {
      hidden.value = val;
      label.textContent = timeLabel(val);
      listEl.querySelectorAll("li").forEach((li) => {
        const sel = li.dataset.value === val;
        li.classList.toggle("bg-[#6ae6fc]/30", sel);
        li.classList.toggle("font-semibold", sel);
        li.classList.toggle("dp-time-active", sel);
      });
    }

    trigger.addEventListener("click", (e) => { e.stopPropagation(); isOpen ? close() : open(); });
    listEl.addEventListener("click", (e) => {
      const li = e.target.closest("li[data-value]");
      if (!li) return;
      setValue(li.dataset.value);
      close();
      if (onChange) onChange(li.dataset.value);
    });
    window.addEventListener("resize", () => { if (isOpen) position(); });

    window._allDatePickers = window._allDatePickers || [];
    window._allDatePickers.push({ close });

    return { setValue, getValue: () => hidden.value };
  }

  // Close all on outside click
  document.addEventListener("click", () => {
    (window._allDatePickers || []).forEach((p) => p.close());
  });

  window.makeDatePicker   = makeDatePicker;
  window.makeTimePicker   = makeTimePicker;
  window.friendlyDateLabel = friendlyLabel;
  window.dpTimeLabel      = timeLabel;
})();

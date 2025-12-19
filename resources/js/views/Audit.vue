<template>
  <div class="audit-page">
    <div id="audit-only"></div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from "vue"
import axios from "axios"
        const APP = { activeTab: 'query', selectedIndustry: 'manufacturing', lastResponse: '', authMode: 'login' };
        const user = { email: 'claudino@shemaksolutions.ca', tier: 'pro', name: 'Claudino Nelson', company: 'Shema K Solutions' };
        const mockUsage = { queries_used: 2847, queries_limit: 10000, hours_saved: 142, avg_response_time: 1.2 };
        const providers = { openai: { name: 'OpenAI', models: ['gpt-4o', 'gpt-4-turbo'], color: '#10B981' }, anthropic: { name: 'Anthropic', models: ['claude-sonnet-4-20250514', 'claude-3-5-haiku-20241022'], color: '#F97316' }, xai: { name: 'xAI Grok', models: ['grok-3'], color: '#3B82F6' }, google: { name: 'Google Gemini', models: ['gemini-1.5-pro'], color: '#EF4444' }, deepseek: { name: 'DeepSeek', models: ['deepseek-chat'], color: '#8B5CF6' } };
        let keys = [{ id: 1, provider: 'anthropic', name: 'Claude Production', model: 'claude-sonnet-4-20250514', is_default: true, total_queries: 1247 }, { id: 2, provider: 'openai', name: 'GPT Backup', model: 'gpt-4o', is_default: false, total_queries: 89 }];
        let teamMembers = [{ id: 1, name: 'Claudino Nelson', email: 'claudino@shemaksolutions.ca', role: 'owner', queries: 1847, badges: ['loto', 'ppe', 'fire'], streak: 12 }, { id: 2, name: 'Sarah Johnson', email: 'sarah@company.com', role: 'admin', queries: 623, badges: ['electrical'], streak: 8 }];
        let queryHistory = [];
        const industries = [
            { id: 'construction', name: 'Construction', color: '#F59E0B', emoji: 'ðŸ—ï¸', prompts: [{ label: 'Fall Protection', query: 'Fall protection requirements for heights above 6 feet', icon: 'ðŸª‚' }, { label: 'Scaffolding', query: 'OSHA scaffolding safety requirements', icon: 'ðŸ—ï¸' }, { label: 'PPE Requirements', query: 'What PPE is required on a construction site?', icon: 'ðŸ¦º' }] },
            { id: 'manufacturing', name: 'Manufacturing', color: '#6366F1', emoji: 'ðŸ­', prompts: [{ label: 'Machine Guarding', query: 'Machine guarding requirements', icon: 'âš™ï¸' }, { label: 'LOTO Procedures', query: 'Lockout/tagout procedures step by step', icon: 'ðŸ”’' }, { label: 'Forklift Operations', query: 'Powered industrial truck safety requirements', icon: 'ðŸšœ' }] },
            { id: 'oilgas', name: 'Oil & Gas', color: '#EF4444', emoji: 'ðŸ›¢ï¸', prompts: [{ label: 'H2S Safety', query: 'H2S safety requirements and exposure limits', icon: 'â˜ ï¸' }, { label: 'Hot Work Permits', query: 'Hot work permit requirements', icon: 'ðŸ”¥' }, { label: 'Confined Space', query: 'Confined space entry requirements', icon: 'ðŸšª' }] },
            { id: 'healthcare', name: 'Healthcare', color: '#EC4899', emoji: 'ðŸ¥', prompts: [{ label: 'Bloodborne Pathogens', query: 'Bloodborne pathogen exposure control', icon: 'ðŸ©¸' }, { label: 'Sharps Safety', query: 'Sharps injury prevention requirements', icon: 'ðŸ’‰' }] },
            { id: 'agriculture', name: 'Agriculture', color: '#84CC16', emoji: 'ðŸŒ¾', prompts: [{ label: 'Tractor Safety', query: 'Agricultural tractor and machinery safety', icon: 'ðŸšœ' }, { label: 'Grain Handling', query: 'Grain bin and silo entry safety', icon: 'ðŸŒ¾' }] },
            { id: 'warehouse', name: 'Warehouse', color: '#8B5CF6', emoji: 'ðŸ“¦', prompts: [{ label: 'Forklift Safety', query: 'Warehouse forklift operation requirements', icon: 'ðŸšœ' }, { label: 'Rack Safety', query: 'Pallet rack safety and inspection', icon: 'ðŸ—„ï¸' }] }
        ];
        const navItems = [{ id: 'query', icon: 'ðŸš€', label: 'Ask K.I.N.G.' }, { id: 'triggers', icon: 'âš¡', label: 'Quick Triggers' }, { id: 'resources', icon: 'ðŸ“š', label: 'Resources' }, { id: 'leaderboard', icon: 'ðŸ†', label: 'Leaderboard' }, { id: 'keys', icon: 'ðŸ”‘', label: 'AI Providers' }, { id: 'team', icon: 'ðŸ‘¥', label: 'Team' }, { id: 'usage', icon: 'ðŸ“Š', label: 'Analytics' }, { id: 'audit', icon: 'ðŸ“‹', label: 'Audit Export' }, { id: 'settings', icon: 'âš™ï¸', label: 'Settings' }];
        const emojiTriggers = [{ emoji: 'ðŸ”¥', action: 'Fire safety and emergency procedures', cat: 'Emergency' }, { emoji: 'âš¡', action: 'Electrical safety requirements', cat: 'Hazards' }, { emoji: 'â˜ ï¸', action: 'Toxic substance handling', cat: 'Hazards' }, { emoji: 'ðŸ”’', action: 'Lockout/Tagout procedures', cat: 'Procedures' }, { emoji: 'ðŸ¦º', action: 'PPE requirements', cat: 'Equipment' }, { emoji: 'ðŸš¨', action: 'Emergency response procedures', cat: 'Emergency' }, { emoji: 'âš ï¸', action: 'Hazard identification', cat: 'Hazards' }, { emoji: 'ðŸ©¹', action: 'First aid procedures', cat: 'Emergency' }, { emoji: 'ðŸ˜·', action: 'Respiratory protection', cat: 'Equipment' }, { emoji: 'ðŸ‘‚', action: 'Hearing protection requirements', cat: 'Equipment' }, { emoji: 'ðŸ‘€', action: 'Eye protection requirements', cat: 'Equipment' }, { emoji: 'ðŸ§¤', action: 'Hand protection requirements', cat: 'Equipment' }, { emoji: 'ðŸ¥¾', action: 'Foot protection requirements', cat: 'Equipment' }, { emoji: 'ðŸªœ', action: 'Ladder safety', cat: 'Equipment' }, { emoji: 'ðŸ—ï¸', action: 'Scaffolding safety', cat: 'Construction' }, { emoji: 'ðŸšœ', action: 'Heavy equipment safety', cat: 'Equipment' }, { emoji: 'ðŸ’¨', action: 'Ventilation requirements', cat: 'Environment' }, { emoji: 'ðŸŒ¡ï¸', action: 'Heat/cold stress prevention', cat: 'Environment' }, { emoji: 'ðŸ“‹', action: 'Safety inspection checklist', cat: 'Procedures' }, { emoji: 'ðŸ“', action: 'Incident reporting procedures', cat: 'Procedures' }];
        const safetyResources = { canada: [{ name: 'CCOHS - Canadian Centre for Occupational Health & Safety', url: 'https://www.ccohs.ca/', desc: 'National centre for workplace health and safety information' }, { name: 'WorkSafeBC', url: 'https://www.worksafebc.com/', desc: 'British Columbia workplace safety' }, { name: 'Alberta OHS', url: 'https://www.alberta.ca/occupational-health-safety.aspx', desc: 'Alberta Occupational Health & Safety' }, { name: 'Saskatchewan WorkSafe', url: 'https://www.worksafesask.ca/', desc: 'Saskatchewan workplace safety' }, { name: 'Safe Work Manitoba', url: 'https://www.safemanitoba.com/', desc: 'Manitoba workplace safety' }, { name: 'Ontario MOL', url: 'https://www.ontario.ca/page/workplace-health-and-safety', desc: 'Ontario Ministry of Labour' }, { name: 'CNESST Quebec', url: 'https://www.cnesst.gouv.qc.ca/en', desc: 'Quebec workplace safety commission' }, { name: 'WorkSafeNB', url: 'https://www.worksafenb.ca/', desc: 'New Brunswick workplace safety' }], usa: [{ name: 'OSHA', url: 'https://www.osha.gov/', desc: 'Occupational Safety and Health Administration' }, { name: 'OSHA Standards', url: 'https://www.osha.gov/laws-regs/regulations/standardnumber', desc: 'Complete OSHA regulations database' }, { name: 'OSHA eTools', url: 'https://www.osha.gov/etools', desc: 'Interactive safety guidance tools' }, { name: 'NIOSH', url: 'https://www.cdc.gov/niosh/', desc: 'National Institute for Occupational Safety & Health' }, { name: 'MSHA', url: 'https://www.msha.gov/', desc: 'Mine Safety and Health Administration' }], international: [{ name: 'ISO 45001', url: 'https://www.iso.org/iso-45001-occupational-health-and-safety.html', desc: 'International OH&S management standard' }, { name: 'ILO OSH', url: 'https://www.ilo.org/safework/', desc: 'International Labour Organization safety resources' }, { name: 'HSE UK', url: 'https://www.hse.gov.uk/', desc: 'UK Health and Safety Executive' }, { name: 'Safe Work Australia', url: 'https://www.safeworkaustralia.gov.au/', desc: 'Australian workplace safety' }], standards: [{ name: 'CSA Group', url: 'https://www.csagroup.org/', desc: 'Canadian Standards Association' }, { name: 'ANSI', url: 'https://www.ansi.org/', desc: 'American National Standards Institute' }, { name: 'NFPA', url: 'https://www.nfpa.org/', desc: 'National Fire Protection Association' }, { name: 'API', url: 'https://www.api.org/', desc: 'American Petroleum Institute standards' }, { name: 'ASTM', url: 'https://www.astm.org/', desc: 'International standards organization' }], training: [{ name: 'OSHA Training Institute', url: 'https://www.osha.gov/otiec', desc: 'Official OSHA training programs' }, { name: 'NSC - National Safety Council', url: 'https://www.nsc.org/', desc: 'Safety training and resources' }, { name: 'Red Cross First Aid', url: 'https://www.redcross.org/take-a-class/first-aid', desc: 'First aid certification' }, { name: 'St. John Ambulance', url: 'https://www.sja.ca/', desc: 'Canadian first aid training' }] };

        function openMobileMenu() { document.getElementById('mobile-drawer').classList.add('open'); document.getElementById('mobile-overlay').classList.add('open'); renderMobileSidebar(); }
        function closeMobileMenu() { document.getElementById('mobile-drawer').classList.remove('open'); document.getElementById('mobile-overlay').classList.remove('open'); }
        function renderMobileSidebar() { document.getElementById('mobile-sidebar-nav').innerHTML = navItems.map(i => `<button onclick="setActiveTab('${i.id}');closeMobileMenu();" class="nav-btn ${APP.activeTab === i.id ? 'active' : ''}">${i.icon} ${i.label}</button>`).join(''); }
        function handleLogout() { document.getElementById('dashboard-page').classList.add('hidden'); document.getElementById('dashboard-page').style.display = 'none'; document.getElementById('landing-page').classList.remove('hidden'); document.getElementById('landing-page').style.display = 'block'; }
        function renderSidebar() { document.getElementById('sidebar-nav').innerHTML = navItems.map(i => `<button onclick="setActiveTab('${i.id}')" class="nav-btn ${APP.activeTab === i.id ? 'active' : ''}">${i.icon} ${i.label}</button>`).join(''); }
        function setActiveTab(tab) { APP.activeTab = tab; renderSidebar(); const titles = { query: 'ðŸ›¡ï¸ Safety Assistant', triggers: 'âš¡ Quick Triggers', resources: 'ðŸ“š Resources', leaderboard: 'ðŸ† Leaderboard', keys: 'ðŸ”‘ AI Providers', team: 'ðŸ‘¥ Team', usage: 'ðŸ“Š Analytics', audit: 'ðŸ“‹ Audit Export', settings: 'âš™ï¸ Settings' }; document.getElementById('page-title').textContent = titles[tab]; renderMainContent(); }
        function renderMainContent() { const c = document.getElementById('main-content'); switch (APP.activeTab) { case 'query': renderQueryTab(c); break; case 'triggers': renderTriggersTab(c); break; case 'resources': renderResourcesTab(c); break; case 'leaderboard': renderLeaderboardTab(c); break; case 'keys': renderKeysTab(c); break; case 'team': renderTeamTab(c); break; case 'usage': renderUsageTab(c); break; case 'audit': renderAuditTab(c); break; case 'settings': renderSettingsTab(c); break; } }
        function getROICard() { const usagePct = (mockUsage.queries_used / mockUsage.queries_limit) * 100; const dailyAvg = mockUsage.queries_used / 18; const daysToLimit = Math.round((mockUsage.queries_limit - mockUsage.queries_used) / dailyAvg); const nagBanner = usagePct >= 90 ? `<div style="padding:12px 20px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;background:linear-gradient(135deg,rgba(239,68,68,0.15) 0%,rgba(220,38,38,0.1) 100%);border:1px solid rgba(239,68,68,0.3);"><div style="display:flex;align-items:center;gap:12px;"><span style="font-size:24px;">ðŸš¨</span><div><div style="font-weight:600;color:#f87171;">Only ${mockUsage.queries_limit - mockUsage.queries_used} queries left!</div><div style="font-size:13px;color:#94a3b8;">Upgrade now to keep your team protected.</div></div></div><button onclick="openUpgradeModal()" class="btn" style="white-space:nowrap;">Upgrade Now</button></div>` : usagePct >= 50 ? `<div style="padding:12px 20px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;background:linear-gradient(135deg,rgba(14,165,233,0.1) 0%,rgba(6,182,212,0.05) 100%);border:1px solid rgba(34,211,238,0.3);"><div style="display:flex;align-items:center;gap:12px;"><span style="font-size:24px;">ðŸ“ˆ</span><div><div style="font-weight:600;color:#22d3ee;">Great momentum! ${Math.round(100 - usagePct)}% remaining</div><div style="font-size:13px;color:#94a3b8;">~${daysToLimit} days until limit at current pace. Preview unlimited?</div></div></div><button onclick="openUpgradeModal()" class="btn-secondary" style="white-space:nowrap;font-size:12px;">Preview Unlimited</button></div>` : ''; return `${nagBanner}<div class="card roi-card" style="margin-bottom:20px;"><div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;"><span style="font-size:24px;">ðŸ’°</span><h3 style="font-size:16px;font-weight:600;">This Month's Impact</h3><span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;margin-left:auto;">Live ROI</span></div><div class="dashboard-stats" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;"><div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#22d3ee;">${mockUsage.hours_saved}</div><div style="font-size:12px;color:#64748b;">Hours Saved</div><div style="font-size:11px;color:#10b981;margin-top:4px;">CA$7,100 value</div></div><div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#10b981;">5</div><div style="font-size:12px;color:#64748b;">Incidents Prevented</div><div style="font-size:11px;color:#10b981;margin-top:4px;">CA$210K impact</div></div><div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#fbbf24;">${mockUsage.avg_response_time}s</div><div style="font-size:12px;color:#64748b;">Avg Response</div><div style="font-size:11px;color:#10b981;margin-top:4px;">â†“8% faster</div></div><div style="text-align:center;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:28px;font-weight:bold;color:#8b5cf6;">CA$45</div><div style="font-size:12px;color:#64748b;">API Cost</div><div style="font-size:11px;color:#64748b;margin-top:4px;">This month</div></div></div><div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(71,85,105,0.3);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;"><div style="font-size:13px;color:#94a3b8;"><strong style="color:#10b981;">Total Value:</strong> CA$217,100</div><div style="display:flex;align-items:center;gap:8px;"><span style="font-size:12px;color:#64748b;">ROI:</span><span style="font-size:16px;font-weight:bold;color:#fbbf24;">2300Ã— ðŸ‘‘</span></div><div style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.3);border-radius:8px;"><span style="font-size:14px;">ðŸ“Š</span><span style="font-size:12px;color:#a78bfa;"><strong>${daysToLimit}</strong> days to limit at current pace</span></div></div></div>`; }
        function renderQueryTab(c) { const ind = industries.find(i => i.id === APP.selectedIndustry); c.innerHTML = `${getROICard()}<div class="query-layout" style="display:flex;gap:24px;"><div class="query-sidebar" style="width:280px;flex-shrink:0;"><div class="card" style="padding:16px;margin-bottom:16px;"><h3 style="font-size:14px;font-weight:600;margin-bottom:12px;">Select Industry</h3><div style="display:flex;flex-direction:column;gap:4px;">${industries.map(i => `<button onclick="selectIndustry('${i.id}')" class="industry-btn" style="background:${APP.selectedIndustry === i.id ? i.color + '20' : 'transparent'};color:${APP.selectedIndustry === i.id ? i.color : '#94a3b8'};">${i.emoji} ${i.name}</button>`).join('')}</div></div>${ind ? `<div class="card" style="padding:16px;"><h3 style="font-size:14px;font-weight:600;margin-bottom:12px;color:${ind.color};">${ind.emoji} ${ind.name} Quick Actions</h3><div style="display:flex;flex-direction:column;gap:6px;">${ind.prompts.map(p => `<button onclick="handleQuery('${p.query.replace(/'/g, "\\'")}')" class="quick-action-btn">${p.icon} ${p.label}</button>`).join('')}</div></div>` : ''}</div><div style="flex:1;"><div class="dashboard-stats" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;"><div class="card" style="padding:16px;"><div style="font-size:12px;color:#64748b;margin-bottom:4px;">Queries Used</div><div style="font-size:24px;font-weight:bold;">${mockUsage.queries_used.toLocaleString()}</div><div style="font-size:11px;color:#64748b;">of ${mockUsage.queries_limit.toLocaleString()}</div><div style="margin-top:8px;height:4px;background:rgba(51,65,85,0.8);border-radius:2px;overflow:hidden;"><div style="height:4px;background:linear-gradient(90deg,#0ea5e9,#06b6d4);border-radius:2px;width:${(mockUsage.queries_used / mockUsage.queries_limit) * 100}%;"></div></div></div><div class="card" style="padding:16px;"><div style="font-size:12px;color:#64748b;margin-bottom:4px;">Active Provider</div><div style="font-size:24px;font-weight:bold;">Claude</div><div style="font-size:11px;color:#64748b;">Anthropic</div></div><div class="card" style="padding:16px;"><div style="font-size:12px;color:#64748b;margin-bottom:4px;">Team Members</div><div style="font-size:24px;font-weight:bold;">${teamMembers.length}</div><div style="font-size:11px;color:#64748b;">Active</div></div><div class="card" style="padding:16px;"><div style="font-size:12px;color:#64748b;margin-bottom:4px;">Avg Response</div><div style="font-size:24px;font-weight:bold;">${mockUsage.avg_response_time}s</div><div style="font-size:11px;color:#10b981;">â†“ 8% faster</div></div></div><div class="card" style="padding:0;overflow:hidden;"><div style="padding:16px 20px;background:rgba(30,41,59,0.8);border-bottom:1px solid rgba(71,85,105,0.3);display:flex;align-items:center;justify-content:space-between;"><div style="display:flex;align-items:center;gap:8px;"><span>ðŸ›¡ï¸</span><span style="font-weight:600;">How can I help you today?</span></div>${ind ? `<span style="font-size:12px;padding:4px 10px;border-radius:6px;background:${ind.color}20;color:${ind.color};">${ind.emoji} ${ind.name}</span>` : ''}</div><div style="padding:20px;"><textarea id="query-input" rows="3" class="input" placeholder="Type your safety question here..."></textarea><div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px;"><div style="display:flex;gap:6px;flex-wrap:wrap;">${['ðŸ”¥ Fire', 'âš¡ Electrical', 'ðŸ”’ LOTO', 'ðŸ¦º PPE', 'ðŸªœ Ladders', 'ðŸ˜· Respiratory'].map(t => `<button onclick="handleQuery('${t.split(' ')[1]} safety requirements')" style="padding:6px 12px;border-radius:6px;border:1px solid rgba(71,85,105,0.3);background:transparent;color:#94a3b8;font-size:12px;cursor:pointer;transition:all 0.2s;">${t}</button>`).join('')}</div><button onclick="submitQuery()" class="btn">ðŸš€ Ask K.I.N.G.</button></div></div><div id="response-area"></div></div></div></div>`; }
        function selectIndustry(id) { APP.selectedIndustry = id; renderMainContent(); }
        function submitQuery() { const input = document.getElementById('query-input'); if (input && input.value.trim()) handleQuery(input.value); }
        function handleQuery(query) { const input = document.getElementById('query-input'); if (input) input.value = query; const ra = document.getElementById('response-area'); if (!ra) return; queryHistory.push({ id: Date.now(), query, timestamp: new Date().toISOString(), industry: APP.selectedIndustry, user: user.name }); const isHighRisk = /loto|lockout|tagout|confined\s*space|electrical|high\s*voltage|arc\s*flash|energized/i.test(query); ra.innerHTML = `<div style="padding:20px;background:rgba(15,23,42,0.5);border-top:1px solid rgba(71,85,105,0.3);"><div style="display:flex;align-items:center;gap:8px;color:#22d3ee;"><span style="display:inline-block;animation:spin 1s linear infinite;">âŸ³</span> Processing with K.I.N.G. Framework v17.0...</div></div>`; setTimeout(() => { APP.lastResponse = `SAFETY REQUIREMENTS â€” ${query.toUpperCase()}\n${'â”'.repeat(50)}\n\nâœ“ Pre-Entry Requirements\n  â€¢ Complete hazard assessment\n  â€¢ Verify all permits are in place\n  â€¢ Ensure proper training documentation\n\nâœ“ Personal Protective Equipment\n  â€¢ Hard hat (Class E or G)\n  â€¢ Safety glasses with side shields\n  â€¢ Steel-toe boots (ASTM F2413)\n  â€¢ High-visibility vest (Class 2 minimum)\n\nâœ“ Procedural Requirements\n  â€¢ Follow all posted safety signs\n  â€¢ Maintain 3-point contact on ladders\n  â€¢ Never bypass safety devices\n\nCOMPLIANCE: OSHA 29 CFR 1910/1926 | CSA Z1000\n\nGenerated by K.I.N.G. Framework v17.0\nÂ© 2025 Shema K Solutions Corporation`; ra.innerHTML = `<div style="padding:20px;background:rgba(15,23,42,0.5);border-top:1px solid rgba(71,85,105,0.3);"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:12px;"><div style="display:flex;align-items:center;gap:8px;color:#10b981;">âœ“ <span style="font-weight:600;">Here's what I found</span><span style="font-size:11px;color:#64748b;margin-left:8px;">1.2s</span>${isHighRisk ? '<span class="badge" style="background:rgba(239,68,68,0.2);color:#ef4444;margin-left:8px;">âš ï¸ HIGH RISK</span>' : ''}</div><div style="display:flex;gap:8px;"><button onclick="exportToPDF()" class="btn-secondary" style="padding:8px 12px;font-size:12px;">ðŸ“„ Export PDF</button><button onclick="exportToJSA()" class="${isHighRisk ? 'btn' : 'btn-secondary'}" style="padding:${isHighRisk ? '10px 16px' : '8px 12px'};font-size:${isHighRisk ? '13px' : '12px'};${isHighRisk ? 'background:linear-gradient(135deg,#ef4444,#dc2626);border:none;animation:pulse-glow 2s ease-in-out infinite;' : ''}">ðŸ“‹ Create JSA${isHighRisk ? ' âš ï¸' : ''}</button></div></div><pre style="white-space:pre-wrap;font-family:ui-monospace,monospace;font-size:13px;line-height:1.6;padding:16px;background:rgba(15,23,42,0.8);border:1px solid ${isHighRisk ? 'rgba(239,68,68,0.3)' : 'rgba(71,85,105,0.3)'};border-radius:10px;color:#e2e8f0;margin:0;overflow-x:auto;">${APP.lastResponse}</pre>${isHighRisk ? `<div style="margin-top:12px;padding:12px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;display:flex;align-items:center;gap:10px;"><span style="font-size:20px;">âš ï¸</span><div><div style="font-weight:600;color:#f87171;">High-Risk Activity Detected</div><div style="font-size:12px;color:#94a3b8;">A Job Safety Analysis (JSA) is strongly recommended before proceeding.</div></div></div>` : ''}</div>`; }, 1500); }
        function exportToPDF() { const blob = new Blob([APP.lastResponse || 'No response'], { type: 'text/plain' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `KING-Report-${new Date().toISOString().split('T')[0]}.txt`; a.click(); URL.revokeObjectURL(url); showToast('ðŸ“„ Report exported!'); }
        function exportToJSA() { const jsa = `JOB SAFETY ANALYSIS (JSA)\nGenerated by K.I.N.G. Framework v17.0\nDate: ${new Date().toLocaleDateString()}\n${'â”'.repeat(40)}\n\nJOB TITLE: ____________________\nDEPARTMENT: ____________________\n\nREQUIRED PPE:\nâ˜ Hard Hat  â˜ Safety Glasses  â˜ Steel-Toe Boots\nâ˜ Hi-Vis Vest  â˜ Gloves  â˜ Hearing Protection\n\nSTEP | HAZARD | CONTROL\n-----|--------|--------\n1.   |        |\n2.   |        |\n3.   |        |\n\n${APP.lastResponse ? '\nREFERENCE:\n' + APP.lastResponse : ''}`; const blob = new Blob([jsa], { type: 'text/plain' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `JSA-${new Date().toISOString().split('T')[0]}.txt`; a.click(); URL.revokeObjectURL(url); showToast('ðŸ“‹ JSA exported!'); }
        function showToast(msg) { const t = document.createElement('div'); t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;z-index:1000;animation:slideUp 0.3s ease-out;box-shadow:0 8px 24px rgba(16,185,129,0.4);'; t.textContent = msg; document.body.appendChild(t); setTimeout(() => t.remove(), 3000); }
        function renderTriggersTab(c) { const cats = [...new Set(emojiTriggers.map(t => t.cat))]; const defaultCats = ['Emergency', 'Hazards', 'Equipment', 'Procedures', 'Environment', 'Construction']; c.innerHTML = `<div style="max-width:900px;margin:0 auto;"><div class="card" style="margin-bottom:24px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:8px;">âš¡ Emoji Quick Triggers</h3><p style="color:#64748b;font-size:14px;margin-bottom:24px;">Click any emoji to instantly get safety guidance. Click âœ• to remove triggers or delete entire categories.</p>${cats.map(cat => `<div style="margin-bottom:20px;"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;"><h4 style="font-size:13px;font-weight:600;color:${cat === 'Custom' || !defaultCats.includes(cat) ? '#22d3ee' : '#94a3b8'};text-transform:uppercase;display:flex;align-items:center;gap:8px;">${cat}${cat === 'Custom' || !defaultCats.includes(cat) ? '<span style="font-size:10px;padding:2px 8px;background:rgba(34,211,238,0.2);border-radius:10px;text-transform:none;">Custom</span>' : ''}<span style="font-size:11px;color:#64748b;font-weight:normal;text-transform:none;">(${emojiTriggers.filter(t => t.cat === cat).length} triggers)</span></h4>${!defaultCats.includes(cat) ? `<button onclick="deleteCategory('${cat}')" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:4px 12px;border-radius:6px;font-size:11px;cursor:pointer;display:flex;align-items:center;gap:4px;">ðŸ—‘ï¸ Delete Category</button>` : ''}</div><div style="display:flex;gap:8px;flex-wrap:wrap;">${emojiTriggers.filter(t => t.cat === cat).map((t, idx) => `<div class="quick-action-btn" style="flex:none;position:relative;"><span onclick="handleTrigger('${t.action.replace(/'/g, "\\'")}')" style="display:flex;align-items:center;gap:8px;cursor:pointer;"><span style="font-size:20px;">${t.emoji}</span><span>${t.action}</span></span><span onclick="event.stopPropagation();deleteTrigger('${t.emoji}','${t.action.replace(/'/g, "\\'")}','${cat}')" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;background:rgba(239,68,68,0.8);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer;color:#fff;opacity:0.7;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">âœ•</span></div>`).join('')}</div></div>`).join('')}</div><div class="card" style="background:linear-gradient(135deg,rgba(139,92,246,0.1) 0%,rgba(99,102,241,0.05) 100%);border:1px solid rgba(139,92,246,0.3);margin-bottom:20px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:8px;">ðŸ“ Create Custom Category</h3><p style="color:#64748b;font-size:13px;margin-bottom:16px;">Organize your triggers into custom categories.</p><div style="display:flex;gap:12px;flex-wrap:wrap;"><input type="text" id="new-category" class="input" placeholder="e.g., Site-Specific, Welding, Chemicals" style="flex:1;min-width:200px;"><button onclick="addCategory()" class="btn">+ Add Category</button></div><div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;"><span style="font-size:12px;color:#64748b;">Current categories:</span>${cats.map(c => `<span style="font-size:12px;padding:4px 10px;background:${defaultCats.includes(c) ? 'rgba(51,65,85,0.5)' : 'rgba(34,211,238,0.2)'};border-radius:6px;color:${defaultCats.includes(c) ? '#cbd5e1' : '#22d3ee'};display:flex;align-items:center;gap:4px;">${c}${!defaultCats.includes(c) ? `<span onclick="deleteCategory('${c}')" style="cursor:pointer;margin-left:4px;">âœ•</span>` : ''}</span>`).join('')}</div></div><div class="card" style="background:linear-gradient(135deg,rgba(14,165,233,0.1) 0%,rgba(6,182,212,0.05) 100%);border:1px solid rgba(34,211,238,0.3);margin-bottom:20px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:8px;">ðŸŽ¯ Create Custom Protocol Trigger</h3><p style="color:#64748b;font-size:13px;margin-bottom:20px;">Build your own emoji shortcuts for frequently used safety queries.</p><div style="display:grid;grid-template-columns:80px 1fr 150px;gap:16px;margin-bottom:16px;"><div><label class="label">Emoji</label><input type="text" id="custom-emoji" class="input" placeholder="ðŸ”§" maxlength="2" style="text-align:center;font-size:24px;padding:12px;"></div><div><label class="label">Safety Query / Protocol Action</label><input type="text" id="custom-action" class="input" placeholder="e.g., Welding safety requirements for confined spaces"></div><div><label class="label">Category</label><select id="custom-category" class="input">${cats.map(c => `<option value="${c}">${c}</option>`).join('')}</select></div></div><div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;"><button onclick="addCustomTrigger()" class="btn">+ Add Custom Trigger</button><div style="display:flex;gap:8px;flex-wrap:wrap;"><span style="font-size:12px;color:#64748b;">Quick pick:</span>${['ðŸ”§', 'â›½', 'ðŸ—ï¸', 'ðŸ’Š', 'ðŸ§¯', 'âš—ï¸', 'ðŸ”Œ', 'ðŸª“', 'ðŸ› ï¸', 'â›‘ï¸'].map(e => `<button onclick="document.getElementById('custom-emoji').value='${e}'" style="background:rgba(51,65,85,0.5);border:1px solid rgba(71,85,105,0.5);border-radius:8px;padding:4px 8px;font-size:16px;cursor:pointer;transition:all 0.2s;">${e}</button>`).join('')}</div></div></div><div class="card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;"><div><div style="font-size:13px;color:#64748b;">Total Triggers</div><div style="font-size:28px;font-weight:bold;color:#22d3ee;">${emojiTriggers.length}</div></div><div><div style="font-size:13px;color:#64748b;">Custom</div><div style="font-size:28px;font-weight:bold;color:#10b981;">${emojiTriggers.filter(t => !defaultCats.includes(t.cat)).length}</div></div><div><div style="font-size:13px;color:#64748b;">Categories</div><div style="font-size:28px;font-weight:bold;color:#fbbf24;">${cats.length}</div></div><div style="display:flex;gap:8px;"><button onclick="exportTriggers()" class="btn-secondary" style="display:flex;align-items:center;gap:8px;">â¬‡ Export</button><button onclick="resetToDefaults()" class="btn-secondary" style="display:flex;align-items:center;gap:8px;background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.3);color:#f87171;">ðŸ”„ Reset Defaults</button></div></div></div>`; }
        function deleteTrigger(emoji, action, cat) { if (confirm(`Remove trigger "${emoji} ${action}"?`)) { const idx = emojiTriggers.findIndex(t => t.emoji === emoji && t.action === action && t.cat === cat); if (idx > -1) { emojiTriggers.splice(idx, 1); showToast('ðŸ—‘ï¸ Trigger removed'); renderTriggersTab(document.getElementById('main-content')); } } }
        function deleteCategory(cat) { const triggersInCat = emojiTriggers.filter(t => t.cat === cat).length; if (confirm(`Delete category "${cat}" and all ${triggersInCat} triggers in it?`)) { emojiTriggers = emojiTriggers.filter(t => t.cat !== cat); showToast(`ðŸ—‘ï¸ Category "${cat}" deleted`); renderTriggersTab(document.getElementById('main-content')); } }
        function resetToDefaults() { if (confirm('Reset all triggers to defaults? This will remove all custom triggers and categories.')) { const defaultTriggers = [{ emoji: 'ðŸ”¥', action: 'Fire safety and emergency procedures', cat: 'Emergency' }, { emoji: 'âš¡', action: 'Electrical safety requirements', cat: 'Hazards' }, { emoji: 'â˜ ï¸', action: 'Toxic substance handling', cat: 'Hazards' }, { emoji: 'ðŸ”’', action: 'Lockout/Tagout procedures', cat: 'Procedures' }, { emoji: 'ðŸ¦º', action: 'PPE requirements', cat: 'Equipment' }, { emoji: 'ðŸš¨', action: 'Emergency response procedures', cat: 'Emergency' }, { emoji: 'âš ï¸', action: 'Hazard identification', cat: 'Hazards' }, { emoji: 'ðŸ©¹', action: 'First aid procedures', cat: 'Emergency' }, { emoji: 'ðŸ˜·', action: 'Respiratory protection', cat: 'Equipment' }, { emoji: 'ðŸ‘‚', action: 'Hearing protection requirements', cat: 'Equipment' }, { emoji: 'ðŸ‘€', action: 'Eye protection requirements', cat: 'Equipment' }, { emoji: 'ðŸ§¤', action: 'Hand protection requirements', cat: 'Equipment' }, { emoji: 'ðŸ¥¾', action: 'Foot protection requirements', cat: 'Equipment' }, { emoji: 'ðŸªœ', action: 'Ladder safety', cat: 'Equipment' }, { emoji: 'ðŸ—ï¸', action: 'Scaffolding safety', cat: 'Construction' }, { emoji: 'ðŸšœ', action: 'Heavy equipment safety', cat: 'Equipment' }, { emoji: 'ðŸ’¨', action: 'Ventilation requirements', cat: 'Environment' }, { emoji: 'ðŸŒ¡ï¸', action: 'Heat/cold stress prevention', cat: 'Environment' }, { emoji: 'ðŸ“‹', action: 'Safety inspection checklist', cat: 'Procedures' }, { emoji: 'ðŸ“', action: 'Incident reporting procedures', cat: 'Procedures' }]; emojiTriggers.length = 0; emojiTriggers.push(...defaultTriggers); showToast('âœ… Triggers reset to defaults'); renderTriggersTab(document.getElementById('main-content')); } }
        function addCategory() { const cat = document.getElementById('new-category').value.trim(); if (!cat) { showToast('âš ï¸ Please enter a category name'); return; } if ([...new Set(emojiTriggers.map(t => t.cat))].includes(cat)) { showToast('âš ï¸ Category already exists'); return; } emojiTriggers.push({ emoji: 'ðŸ“Œ', action: `${cat} placeholder - edit or delete`, cat: cat }); document.getElementById('new-category').value = ''; showToast('âœ… Category created!'); renderTriggersTab(document.getElementById('main-content')); }
        function addCustomTrigger() { const emoji = document.getElementById('custom-emoji').value.trim(), action = document.getElementById('custom-action').value.trim(), cat = document.getElementById('custom-category').value; if (!emoji) { showToast('âš ï¸ Please select an emoji'); return; } if (!action) { showToast('âš ï¸ Please enter a safety query'); return; } if (emojiTriggers.some(t => t.emoji === emoji && t.action.toLowerCase() === action.toLowerCase())) { showToast('âš ï¸ Trigger already exists'); return; } emojiTriggers.push({ emoji, action, cat: cat || 'Custom' }); document.getElementById('custom-emoji').value = ''; document.getElementById('custom-action').value = ''; showToast('âœ… Custom trigger added!'); renderTriggersTab(document.getElementById('main-content')); }
        function exportTriggers() { const content = `K.I.N.G. PROTOCOL TRIGGERS EXPORT\nGenerated: ${new Date().toISOString()}\nOrganization: ${user.company}\n${'â”'.repeat(50)}\n\nTOTAL TRIGGERS: ${emojiTriggers.length}\n\n${[...new Set(emojiTriggers.map(t => t.cat))].map(cat => `\n${cat.toUpperCase()}\n${emojiTriggers.filter(t => t.cat === cat).map(t => '  ' + t.emoji + ' ' + t.action).join('\n')}`).join('\n')}\n\n${'â”'.repeat(50)}\nGenerated by K.I.N.G. Framework v17.0\nÂ© 2025 Shema K Solutions Corporation`; const blob = new Blob([content], { type: 'text/plain' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `KING-Triggers-${new Date().toISOString().split('T')[0]}.txt`; a.click(); URL.revokeObjectURL(url); showToast('ðŸ“‹ Triggers exported!'); }
        function handleTrigger(action) { setActiveTab('query'); setTimeout(() => handleQuery(action), 100); }
        function renderResourcesTab(c) { c.innerHTML = `<div style="max-width:1000px;margin:0 auto;"><div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,rgba(14,165,233,0.1) 0%,rgba(6,182,212,0.05) 100%);border:1px solid rgba(34,211,238,0.3);"><div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;"><span style="font-size:28px;">ðŸ“š</span><h2 style="font-size:20px;font-weight:600;">Safety Regulatory Resources</h2></div><p style="color:#94a3b8;">Quick access to official safety regulations, standards organizations, and training resources. All links open in a new tab.</p></div><div class="card" style="margin-bottom:20px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:10px;"><span style="font-size:24px;">ðŸ‡¨ðŸ‡¦</span> Canadian Safety Regulators</h3><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">${safetyResources.canada.map(r => `<a href="${r.url}" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;border:1px solid rgba(71,85,105,0.3);background:rgba(15,23,42,0.5);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.borderColor='rgba(239,68,68,0.5)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='rgba(71,85,105,0.3)';this.style.transform='none'"><div style="width:36px;height:36px;border-radius:8px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#ef4444;">ðŸ›¡ï¸</div><div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:13px;color:#e2e8f0;margin-bottom:2px;">${r.name}</div><div style="font-size:11px;color:#64748b;">${r.desc}</div></div><span style="flex-shrink:0;color:#64748b;">â†—</span></a>`).join('')}</div></div><div class="card" style="margin-bottom:20px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:10px;"><span style="font-size:24px;">ðŸ‡ºðŸ‡¸</span> US Safety Regulators (OSHA)</h3><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">${safetyResources.usa.map(r => `<a href="${r.url}" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;border:1px solid rgba(71,85,105,0.3);background:rgba(15,23,42,0.5);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.borderColor='rgba(59,130,246,0.5)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='rgba(71,85,105,0.3)';this.style.transform='none'"><div style="width:36px;height:36px;border-radius:8px;background:rgba(59,130,246,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#3b82f6;">ðŸ›ï¸</div><div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:13px;color:#e2e8f0;margin-bottom:2px;">${r.name}</div><div style="font-size:11px;color:#64748b;">${r.desc}</div></div><span style="flex-shrink:0;color:#64748b;">â†—</span></a>`).join('')}</div></div><div class="card" style="margin-bottom:20px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:10px;"><span style="font-size:24px;">ðŸŒ</span> International Standards</h3><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">${safetyResources.international.map(r => `<a href="${r.url}" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;border:1px solid rgba(71,85,105,0.3);background:rgba(15,23,42,0.5);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.borderColor='rgba(139,92,246,0.5)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='rgba(71,85,105,0.3)';this.style.transform='none'"><div style="width:36px;height:36px;border-radius:8px;background:rgba(139,92,246,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#8b5cf6;">ðŸŒ</div><div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:13px;color:#e2e8f0;margin-bottom:2px;">${r.name}</div><div style="font-size:11px;color:#64748b;">${r.desc}</div></div><span style="flex-shrink:0;color:#64748b;">â†—</span></a>`).join('')}</div></div><div class="card" style="margin-bottom:20px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:10px;"><span style="font-size:24px;">ðŸ“</span> Standards Organizations</h3><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">${safetyResources.standards.map(r => `<a href="${r.url}" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;border:1px solid rgba(71,85,105,0.3);background:rgba(15,23,42,0.5);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.borderColor='rgba(251,191,36,0.5)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='rgba(71,85,105,0.3)';this.style.transform='none'"><div style="width:36px;height:36px;border-radius:8px;background:rgba(251,191,36,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fbbf24;">ðŸ“‹</div><div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:13px;color:#e2e8f0;margin-bottom:2px;">${r.name}</div><div style="font-size:11px;color:#64748b;">${r.desc}</div></div><span style="flex-shrink:0;color:#64748b;">â†—</span></a>`).join('')}</div></div><div class="card"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:10px;"><span style="font-size:24px;">ðŸŽ“</span> Training & Certification</h3><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">${safetyResources.training.map(r => `<a href="${r.url}" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;border:1px solid rgba(71,85,105,0.3);background:rgba(15,23,42,0.5);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.borderColor='rgba(16,185,129,0.5)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='rgba(71,85,105,0.3)';this.style.transform='none'"><div style="width:36px;height:36px;border-radius:8px;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#10b981;">ðŸŽ“</div><div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:13px;color:#e2e8f0;margin-bottom:2px;">${r.name}</div><div style="font-size:11px;color:#64748b;">${r.desc}</div></div><span style="flex-shrink:0;color:#64748b;">â†—</span></a>`).join('')}</div></div></div>`; }
        function renderLeaderboardTab(c) { const sorted = [...teamMembers].sort((a, b) => b.queries - a.queries); c.innerHTML = `<div style="max-width:800px;margin:0 auto;"><div class="card" style="background:linear-gradient(135deg,rgba(251,191,36,0.1) 0%,rgba(245,158,11,0.05) 100%);border:1px solid rgba(251,191,36,0.3);margin-bottom:24px;"><div style="display:flex;align-items:center;gap:16px;"><div style="font-size:48px;">ðŸ†</div><div><h2 style="font-size:20px;font-weight:bold;margin-bottom:4px;">Team Safety Champions</h2><p style="color:#94a3b8;">More queries = more lives protected.</p></div></div></div><div class="card" style="padding:0;overflow:hidden;margin-bottom:24px;"><div style="padding:16px 20px;background:rgba(30,41,59,0.8);border-bottom:1px solid rgba(71,85,105,0.3);"><h3 style="font-weight:600;">This Month's Rankings</h3></div><div style="padding:16px;display:flex;flex-direction:column;gap:12px;">${sorted.map((m, i) => { const rc = i === 0 ? 'gold' : i === 1 ? 'silver' : i === 2 ? 'bronze' : ''; const ri = i === 0 ? 'ðŸ¥‡' : i === 1 ? 'ðŸ¥ˆ' : i === 2 ? 'ðŸ¥‰' : `#${i + 1}`; return `<div class="leaderboard-item ${rc}"><div style="font-size:20px;width:40px;text-align:center;">${ri}</div><div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#06b6d4);display:flex;align-items:center;justify-content:center;font-weight:600;">${m.name[0]}</div><div style="flex:1;"><div style="font-weight:600;font-size:14px;">${m.name}</div><div style="display:flex;gap:6px;margin-top:4px;">${(m.badges || []).map(b => `<span class="achievement-badge ${b}">${b === 'loto' ? 'ðŸ”’ LOTO' : b === 'ppe' ? 'ðŸ¦º PPE' : b === 'fire' ? 'ðŸ”¥ Fire' : 'âš¡ Electrical'}</span>`).join('')}</div></div><div style="text-align:right;"><div style="font-size:20px;font-weight:bold;color:#22d3ee;">${m.queries}</div><div style="font-size:11px;color:#64748b;">queries</div></div><div style="text-align:center;padding-left:12px;border-left:1px solid rgba(71,85,105,0.3);"><div style="font-size:16px;">ðŸ”¥</div><div style="font-size:11px;color:#f97316;">${m.streak || 0} day streak</div></div></div>`; }).join('')}</div></div><div class="card" style="background:linear-gradient(135deg,rgba(14,165,233,0.1) 0%,rgba(6,182,212,0.05) 100%);border:1px solid rgba(34,211,238,0.3);"><div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;"><div style="font-size:32px;">ðŸŽ</div><div style="flex:1;"><h3 style="font-weight:600;margin-bottom:4px;">Invite Challenge Active!</h3><p style="color:#94a3b8;font-size:13px;">Invite 2 more team members and everyone gets a <span style="color:#10b981;font-weight:600;">20% discount</span> on upgrades.</p></div><button onclick="openInviteModal()" class="btn">Invite</button></div></div></div>`; }
        function renderKeysTab(c) { c.innerHTML = `<div style="max-width:700px;margin:0 auto;"><div class="card" style="margin-bottom:20px;background:linear-gradient(135deg,rgba(59,130,246,0.1) 0%,rgba(139,92,246,0.05) 100%);border:1px solid rgba(59,130,246,0.3);"><h3 style="font-size:14px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;">ðŸ“Š Provider Performance Comparison</h3><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">${Object.entries(providers).slice(0, 4).map(([k, p]) => `<div style="padding:12px;background:rgba(15,23,42,0.5);border-radius:10px;border:1px solid ${keys.find(key => key.provider === k && key.is_default) ? 'rgba(34,211,238,0.5)' : 'rgba(71,85,105,0.3)'};"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;"><span style="font-weight:600;font-size:13px;color:${p.color};">${p.name}</span>${keys.find(key => key.provider === k && key.is_default) ? '<span class="badge" style="background:rgba(34,211,238,0.2);color:#22d3ee;font-size:10px;">Active</span>' : ''}</div><div style="font-size:12px;color:#64748b;">Avg: 1.2s</div></div>`).join('')}</div><div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(71,85,105,0.3);"><p style="font-size:12px;color:#94a3b8;">ðŸ’¡ <strong>Tip:</strong> Claude is currently fastest. Consider Grok for predictive safety edge.</p></div></div><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;"><div><p style="color:#94a3b8;">Your API keys are AES-256 encrypted</p><p style="font-size:12px;color:#64748b;margin-top:4px;">This month: CA$45 across all providers</p></div><button onclick="toggleAddKey()" class="btn">+ Add Provider</button></div><div id="add-key-form" class="hidden card" style="border:1px solid rgba(34,211,238,0.3);margin-bottom:20px;"><h3 style="font-weight:600;margin-bottom:16px;">Connect Provider</h3><div style="display:flex;flex-direction:column;gap:12px;"><div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"><div><label class="label">Provider</label><select id="new-key-provider" class="input" onchange="updateModels()">${Object.entries(providers).map(([k, v]) => `<option value="${k}">${v.name}</option>`).join('')}</select></div><div><label class="label">Model</label><select id="new-key-model" class="input">${providers.openai.models.map(m => `<option value="${m}">${m}</option>`).join('')}</select></div></div><div><label class="label">Name</label><input type="text" id="new-key-name" class="input" placeholder="e.g., Production Claude"></div><div><label class="label">API Key</label><input type="password" id="new-key-api" class="input" style="font-family:monospace;" placeholder="sk-..."></div><div style="display:flex;gap:12px;"><button onclick="saveNewKey()" class="btn">Save</button><button onclick="toggleAddKey()" class="btn-secondary">Cancel</button></div></div></div><div style="display:flex;flex-direction:column;gap:12px;">${keys.map(k => `<div class="card" style="border:${k.is_default ? '1px solid rgba(34,211,238,0.5)' : '1px solid rgba(71,85,105,0.5)'};display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;"><div style="display:flex;align-items:center;gap:16px;"><div style="width:48px;height:48px;border-radius:12px;background:${providers[k.provider]?.color}20;display:flex;align-items:center;justify-content:center;color:${providers[k.provider]?.color};">ðŸ¤–</div><div><div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;"><span style="font-weight:600;">${k.name}</span>${k.is_default ? '<span class="badge" style="background:rgba(34,211,238,0.2);color:#22d3ee;">Default</span>' : ''}</div><div style="font-size:13px;color:#64748b;">${providers[k.provider]?.name} â€¢ ${k.model}</div><div style="font-size:12px;color:#64748b;">${k.total_queries.toLocaleString()} queries</div></div></div><div style="display:flex;align-items:center;gap:8px;">${!k.is_default ? `<button onclick="setDefaultKey(${k.id})" class="btn-secondary" style="padding:8px 16px;">Set Default</button>` : ''}<button onclick="deleteKey(${k.id})" style="background:none;border:none;color:#64748b;cursor:pointer;">ðŸ—‘ï¸</button></div></div>`).join('')}</div></div>`; }
        function toggleAddKey() { document.getElementById('add-key-form').classList.toggle('hidden'); }
        function updateModels() { const p = document.getElementById('new-key-provider').value; document.getElementById('new-key-model').innerHTML = providers[p].models.map(m => `<option value="${m}">${m}</option>`).join(''); }
        function saveNewKey() { const p = document.getElementById('new-key-provider').value, m = document.getElementById('new-key-model').value, n = document.getElementById('new-key-name').value; if (n) { keys.push({ id: Date.now(), provider: p, name: n, model: m, is_default: keys.length === 0, total_queries: 0 }); toggleAddKey(); renderMainContent(); showToast('âœ… Provider added!'); } }
        function setDefaultKey(id) { keys = keys.map(k => ({ ...k, is_default: k.id === id })); renderMainContent(); showToast('âœ… Default updated!'); }
        function deleteKey(id) { keys = keys.filter(k => k.id !== id); renderMainContent(); showToast('ðŸ—‘ï¸ Removed.'); }
        function renderTeamTab(c) { const roles = { owner: { label: 'Owner', color: '#F59E0B' }, admin: { label: 'Admin', color: '#8B5CF6' }, member: { label: 'Member', color: '#3B82F6' } }; c.innerHTML = `<div style="max-width:800px;margin:0 auto;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;"><div><p style="color:#94a3b8;">Manage your team members</p><p style="font-size:12px;color:#64748b;margin-top:4px;">${teamMembers.length} of 5 seats used</p></div><button onclick="openInviteModal()" class="btn">+ Invite</button></div><div class="card" style="padding:0;overflow:hidden;">${teamMembers.map((m, i) => `<div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:${i < teamMembers.length - 1 ? '1px solid rgba(71,85,105,0.3)' : 'none'};flex-wrap:wrap;gap:12px;"><div style="display:flex;align-items:center;gap:12px;"><div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#06b6d4);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;">${m.name[0]}</div><div><div style="font-weight:500;font-size:14px;">${m.name}</div><div style="font-size:13px;color:#64748b;">${m.email}</div><div style="display:flex;gap:4px;margin-top:4px;">${(m.badges || []).map(b => `<span class="achievement-badge ${b}" style="font-size:10px;padding:2px 6px;">${b === 'loto' ? 'ðŸ”’' : b === 'ppe' ? 'ðŸ¦º' : b === 'fire' ? 'ðŸ”¥' : 'âš¡'}</span>`).join('')}</div></div></div><div style="display:flex;align-items:center;gap:12px;"><span class="badge" style="background:${(roles[m.role] || roles.member).color}20;color:${(roles[m.role] || roles.member).color};">${(roles[m.role] || roles.member).label}</span><span style="color:#64748b;font-size:13px;">${m.queries} queries</span>${m.role !== 'owner' ? `<button onclick="removeMember(${m.id})" style="background:none;border:none;color:#64748b;cursor:pointer;padding:4px;">âœ•</button>` : ''}</div></div>`).join('')}</div><div class="card" style="margin-top:20px;background:linear-gradient(135deg,rgba(14,165,233,0.1) 0%,rgba(6,182,212,0.05) 100%);border:1px solid rgba(34,211,238,0.3);"><div style="display:flex;align-items:center;gap:16px;"><div style="font-size:32px;">ðŸŽ</div><div style="flex:1;"><h3 style="font-weight:600;margin-bottom:4px;">Invite Challenge Active!</h3><p style="color:#94a3b8;font-size:13px;">Invite 2 more team members and everyone gets a <span style="color:#10b981;font-weight:600;">20% discount</span> on upgrades.</p></div><button onclick="openInviteModal()" class="btn">Invite Now</button></div></div></div>`; }
        function removeMember(id) { if (confirm('Remove this team member?')) { teamMembers = teamMembers.filter(m => m.id !== id); renderMainContent(); showToast('Team member removed.'); } }
        function openInviteModal() { document.getElementById('invite-modal').classList.remove('hidden'); }
        function closeInviteModal() { document.getElementById('invite-modal').classList.add('hidden'); }
        function openUpgradeModal() { document.getElementById('upgrade-modal').classList.remove('hidden'); }
        function closeUpgradeModal() { document.getElementById('upgrade-modal').classList.add('hidden'); }
        function processUpgrade() { closeUpgradeModal(); showToast('ðŸŽ‰ Upgrade successful! Welcome to Unlimited.'); }
        function handleInvite() { const email = document.getElementById('invite-email').value, role = document.getElementById('invite-role').value; if (email) { teamMembers.push({ id: Date.now(), name: 'Pending User', email, role, queries: 0, badges: [], streak: 0 }); document.getElementById('invite-email').value = ''; closeInviteModal(); renderMainContent(); showToast('ðŸ“§ Invitation sent!'); } }
        function renderUsageTab(c) { c.innerHTML = `<div style="max-width:800px;margin:0 auto;">${getROICard()}<div class="dashboard-stats" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;"><div class="card"><div style="font-size:12px;color:#64748b;">Total Queries</div><div style="font-size:28px;font-weight:bold;margin:8px 0;">2,847</div><div style="font-size:12px;color:#10b981;">â†‘ 12%</div></div><div class="card"><div style="font-size:12px;color:#64748b;">Avg Response</div><div style="font-size:28px;font-weight:bold;margin:8px 0;">1.2s</div><div style="font-size:12px;color:#22d3ee;">â†“ 8%</div></div><div class="card"><div style="font-size:12px;color:#64748b;">Success Rate</div><div style="font-size:28px;font-weight:bold;margin:8px 0;">99.2%</div><div style="font-size:12px;color:#10b981;">â†‘ 0.3%</div></div><div class="card"><div style="font-size:12px;color:#64748b;">Hours Saved</div><div style="font-size:28px;font-weight:bold;margin:8px 0;">142</div><div style="font-size:12px;color:#10b981;">â†‘ 15%</div></div></div><div class="card" style="margin-bottom:24px;"><h3 style="font-weight:600;margin-bottom:20px;">Daily Usage</h3><div style="display:flex;align-items:flex-end;justify-content:space-between;height:160px;gap:8px;">${[120, 180, 95, 220, 150, 280, 195].map((val, i) => `<div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;"><div style="width:100%;border-radius:6px 6px 0 0;background:linear-gradient(180deg,#0ea5e9 0%,#06b6d4 100%);height:${(val / 280) * 100}%;"></div><span style="font-size:11px;color:#64748b;">${['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'][i]}</span></div>`).join('')}</div></div><div class="card" style="margin-bottom:24px;"><h3 style="font-weight:600;margin-bottom:20px;">Provider Performance</h3><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">${Object.entries(providers).slice(0, 4).map(([k, p]) => `<div style="padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;border:1px solid ${keys.find(key => key.provider === k && key.is_default) ? 'rgba(34,211,238,0.5)' : 'rgba(71,85,105,0.3)'};"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;"><span style="font-weight:600;font-size:13px;color:${p.color};">${p.name}</span>${keys.find(key => key.provider === k && key.is_default) ? '<span class="badge" style="background:rgba(34,211,238,0.2);color:#22d3ee;font-size:10px;">Active</span>' : ''}</div><div style="font-size:12px;color:#64748b;">Avg: 1.2s response</div></div>`).join('')}</div></div><div class="card"><h3 style="font-weight:600;margin-bottom:20px;">Top Queries by Category</h3><div style="display:flex;flex-direction:column;gap:12px;">${[{ cat: 'PPE Requirements', pct: 35, color: '#8b5cf6' }, { cat: 'LOTO Procedures', pct: 28, color: '#fbbf24' }, { cat: 'Fall Protection', pct: 22, color: '#ef4444' }, { cat: 'Fire Safety', pct: 15, color: '#10b981' }].map(q => `<div><div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="font-size:13px;color:#cbd5e1;">${q.cat}</span><span style="font-size:13px;color:#64748b;">${q.pct}%</span></div><div style="height:8px;background:rgba(51,65,85,0.8);border-radius:4px;overflow:hidden;"><div style="height:8px;background:${q.color};border-radius:4px;width:${q.pct}%;"></div></div></div>`).join('')}</div></div></div>`; }
        function renderAuditTab(c) { c.innerHTML = `<div style="max-width:800px;margin:0 auto;"><div class="card" style="background:linear-gradient(135deg,rgba(16,185,129,0.1) 0%,rgba(5,150,105,0.05) 100%);border:1px solid rgba(16,185,129,0.3);margin-bottom:24px;"><div style="display:flex;align-items:center;gap:16px;"><div style="font-size:48px;">📋</div><div><h2 style="font-size:20px;font-weight:bold;margin-bottom:4px;">Compliance Audit Export</h2><p style="color:#94a3b8;">Export your complete query log for OSHA audits and ISO 45001 compliance.</p></div></div></div><div class="card" style="margin-bottom:20px;"><h3 style="font-weight:600;margin-bottom:16px;">Export Options</h3><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;"><div style="padding:20px;background:rgba(15,23,42,0.5);border-radius:12px;border:1px solid rgba(71,85,105,0.3);text-align:center;"><div style="font-size:32px;margin-bottom:12px;">📄</div><h4 style="font-weight:600;margin-bottom:8px;">Full Query Log</h4><p style="font-size:13px;color:#64748b;margin-bottom:16px;">All queries with timestamps</p><button onclick="exportAuditLog('full')" class="btn" style="width:100%;justify-content:center;">⬇ Export PDF</button></div><div style="padding:20px;background:rgba(15,23,42,0.5);border-radius:12px;border:1px solid rgba(71,85,105,0.3);text-align:center;"><div style="font-size:32px;margin-bottom:12px;">📊</div><h4 style="font-weight:600;margin-bottom:8px;">Summary Report</h4><p style="font-size:13px;color:#64748b;margin-bottom:16px;">Usage statistics and metrics</p><button onclick="exportAuditLog('summary')" class="btn" style="width:100%;justify-content:center;">⬇ Export PDF</button></div></div></div><div class="card"><h3 style="font-weight:600;margin-bottom:16px;">Recent Query History (${queryHistory.length} entries)</h3><div style="max-height:400px;overflow-y:auto;">${queryHistory.length === 0 ? `<div style="text-align:center;padding:40px;color:#64748b;"><div style="font-size:32px;margin-bottom:12px;">🔍</div><p>No queries yet. Start asking K.I.N.G. safety questions.</p></div>` : queryHistory.slice(-10).reverse().map(q => `<div style="padding:12px;border-bottom:1px solid rgba(71,85,105,0.3);"><div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span style="font-weight:600;font-size:13px;">${q.query.substring(0, 50)}${q.query.length > 50 ? '...' : ''}</span><span style="font-size:11px;color:#64748b;">${new Date(q.timestamp).toLocaleString()}</span></div><div style="display:flex;gap:8px;"><span class="badge" style="background:rgba(99,102,241,0.2);color:#818cf8;font-size:10px;">${q.industry}</span><span style="font-size:11px;color:#64748b;">by ${q.user}</span></div></div>`).join('')}</div></div></div>`; }
        async function loadQueryHistory() {
          try {
            const { data } = await axios.get("/api/ask-king/history");
            queryHistory = Array.isArray(data)
              ? data.map((item) => ({
                  query: item.message || "",
                  response: item.response || "",
                  user: "Owner",
                  industry: "General",
                  timestamp: item.created_at || new Date().toISOString(),
                }))
              : [];
          } catch (error) {
            console.error("Failed to load query history", error);
            queryHistory = [];
          }
        }
        function exportAuditLog(type) { const header = `K.I.N.G. FRAMEWORK AUDIT LOG\nGenerated: ${new Date().toISOString()}\nOrganization: ${user.company}\n${'-'.repeat(50)}\n\n`; let content = header; if (type === 'full') { content += 'FULL QUERY LOG\n' + '-'.repeat(50) + '\n\n'; queryHistory.forEach((q, i) => { content += `[${i + 1}] ${new Date(q.timestamp).toLocaleString()}\nUser: ${q.user}\nIndustry: ${q.industry}\nQuery: ${q.query}\nResponse: ${q.response || ''}\n\n`; }); } else { content += `SUMMARY REPORT\n${'-'.repeat(50)}\n\nTotal Queries: ${mockUsage.queries_used}\nHours Saved: ${mockUsage.hours_saved}\nActive Team Members: ${teamMembers.length}\nAverage Response Time: ${mockUsage.avg_response_time}s\n\nCOMPLIANCE STATUS: OK OSHA Ready\n`; } content += `\n${'-'.repeat(50)}\nGenerated by K.I.N.G. Framework v17.0\n(c) 2025 Shema K Solutions Corporation`; const pdfBlob = buildPdfBlob(content); const url = URL.createObjectURL(pdfBlob); const a = document.createElement('a'); a.href = url; a.download = `KING-Audit-${type}-${new Date().toISOString().split('T')[0]}.pdf`; a.click(); URL.revokeObjectURL(url); showToast('📋 Audit log exported!'); }

        function buildPdfBlob(text) {
          const lines = wrapText(normalizePdfText(text), 90);
          const pageLineLimit = 55;
          const pages = [];
          for (let i = 0; i < lines.length; i += pageLineLimit) {
            pages.push(lines.slice(i, i + pageLineLimit));
          }
          if (!pages.length) {
            pages.push([""]);
          }

          const objects = [];
          objects.push("1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n");

          const pageKids = [];
          let objIndex = 3;
          const fontObjId = objIndex + pages.length * 2;

          pages.forEach(() => {
            pageKids.push(`${objIndex} 0 R`);
            objIndex += 2;
          });

          objects.push(
            `2 0 obj\n<< /Type /Pages /Kids [${pageKids.join(" ")}] /Count ${pages.length} >>\nendobj\n`
          );

          let contentObjId = 4;
          pages.forEach((pageLines, pageIndex) => {
            const pageObjId = 3 + pageIndex * 2;
            contentObjId = pageObjId + 1;
            const contentStream = buildPdfContentStream(pageLines);
            objects.push(
              `${pageObjId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 ${fontObjId} 0 R >> >> /Contents ${contentObjId} 0 R >>\nendobj\n`
            );
            objects.push(
              `${contentObjId} 0 obj\n<< /Length ${contentStream.length} >>\nstream\n${contentStream}\nendstream\nendobj\n`
            );
          });

          objects.push(`${fontObjId} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n`);

          let offset = 0;
          let pdf = "%PDF-1.4\n";
          const offsets = [0];
          offset = pdf.length;
          objects.forEach((obj) => {
            offsets.push(offset);
            pdf += obj;
            offset = pdf.length;
          });

          const xrefStart = pdf.length;
          pdf += `xref\n0 ${offsets.length}\n`;
          pdf += "0000000000 65535 f \n";
          for (let i = 1; i < offsets.length; i++) {
            pdf += offsets[i].toString().padStart(10, "0") + " 00000 n \n";
          }
          pdf += `trailer\n<< /Size ${offsets.length} /Root 1 0 R >>\nstartxref\n${xrefStart}\n%%EOF\n`;

          return new Blob([pdf], { type: "application/pdf" });
        }

        function buildPdfContentStream(lines) {
          const startY = 760;
          const lineHeight = 12;
          let stream = "BT\n/F1 10 Tf\n" + lineHeight + " TL\n72 " + startY + " Td\n";
          lines.forEach((line, index) => {
            if (index > 0) {
              stream += "T*\n";
            }
            stream += "(" + escapePdfText(line) + ") Tj\n";
          });
          stream += "ET";
          return stream;
        }

        function escapePdfText(line) {
          return line.replace(/\\/g, "\\\\").replace(/\(/g, "\\(").replace(/\)/g, "\\)");
        }

        function normalizePdfText(text) {
          return String(text)
            .replace(/\r\n/g, "\n")
            .replace(/[^\x20-\x7E\n]/g, ""); // strip non-ASCII for PDF
        }

        function wrapText(text, width) {
          const lines = [];
          text.split("\n").forEach((rawLine) => {
            let line = rawLine;
            while (line.length > width) {
              lines.push(line.slice(0, width));
              line = line.slice(width);
            }
            lines.push(line);
          });
          return lines;
        }
        function renderSettingsTab(c) { c.innerHTML = `<div style="max-width:600px;margin:0 auto;"><div class="card" style="margin-bottom:20px;"><h3 style="font-weight:600;margin-bottom:20px;">Account Settings</h3><div style="display:flex;flex-direction:column;gap:16px;"><div><label class="label">Name</label><input type="text" value="${user.name}" class="input" id="settings-name"></div><div><label class="label">Email</label><input type="email" value="${user.email}" class="input" id="settings-email"></div><div><label class="label">Company</label><input type="text" value="${user.company}" class="input" id="settings-company"></div><button onclick="saveSettings()" class="btn" style="align-self:flex-start;">Save Changes</button></div></div><div class="card" style="margin-bottom:20px;"><h3 style="font-weight:600;margin-bottom:20px;">Notification Preferences</h3><div style="display:flex;flex-direction:column;gap:12px;"><label style="display:flex;align-items:center;gap:12px;cursor:pointer;"><input type="checkbox" checked style="width:18px;height:18px;accent-color:#0ea5e9;"><span style="color:#cbd5e1;">Email notifications for new safety alerts</span></label><label style="display:flex;align-items:center;gap:12px;cursor:pointer;"><input type="checkbox" checked style="width:18px;height:18px;accent-color:#0ea5e9;"><span style="color:#cbd5e1;">Weekly usage summary</span></label><label style="display:flex;align-items:center;gap:12px;cursor:pointer;"><input type="checkbox" style="width:18px;height:18px;accent-color:#0ea5e9;"><span style="color:#cbd5e1;">Marketing updates</span></label></div></div><div class="card" style="margin-bottom:20px;background:linear-gradient(135deg,rgba(14,165,233,0.1) 0%,rgba(6,182,212,0.05) 100%);border:1px solid rgba(34,211,238,0.3);"><h3 style="font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px;">ðŸ’³ Subscription Management</h3><div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:20px;"><div><div style="font-size:24px;font-weight:bold;color:#22d3ee;">Professional Plan</div><div style="color:#64748b;font-size:14px;">$99/month â€¢ Renews Jan 15, 2025</div></div><span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;">Active</span></div><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;"><div style="padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:12px;color:#64748b;margin-bottom:4px;">Queries This Month</div><div style="font-size:20px;font-weight:bold;">${mockUsage.queries_used.toLocaleString()} / ${mockUsage.queries_limit.toLocaleString()}</div></div><div style="padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="font-size:12px;color:#64748b;margin-bottom:4px;">Next Billing Date</div><div style="font-size:20px;font-weight:bold;">Jan 15, 2025</div></div></div><div style="display:flex;gap:12px;flex-wrap:wrap;"><button onclick="openStripePortal('billing')" class="btn">Manage Billing</button><button onclick="openStripePortal('payment')" class="btn-secondary">Update Payment Method</button><button onclick="openStripePortal('invoices')" class="btn-secondary">View Invoices</button></div><div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(71,85,105,0.3);"><div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;"><span style="font-size:16px;">ðŸ“Š</span><span style="font-weight:600;">Plan Comparison</span></div><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">${[{ name: 'Starter', price: '$0', queries: '100/mo', current: false }, { name: 'Professional', price: '$99', queries: '10,000/mo', current: true }, { name: 'Enterprise', price: '$299', queries: 'Unlimited', current: false }].map(p => `<div style="padding:12px;background:${p.current ? 'rgba(34,211,238,0.1)' : 'rgba(15,23,42,0.5)'};border-radius:10px;border:1px solid ${p.current ? 'rgba(34,211,238,0.5)' : 'rgba(71,85,105,0.3)'};text-align:center;"><div style="font-weight:600;font-size:14px;color:${p.current ? '#22d3ee' : '#cbd5e1'};margin-bottom:4px;">${p.name}</div><div style="font-size:18px;font-weight:bold;margin-bottom:4px;">${p.price}<span style="font-size:12px;color:#64748b;">/mo</span></div><div style="font-size:11px;color:#64748b;">${p.queries}</div>${p.current ? '<div style="font-size:10px;color:#22d3ee;margin-top:4px;">Current Plan</div>' : p.name === 'Enterprise' ? `<button onclick="openUpgradeModal()" style="margin-top:8px;padding:4px 12px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border:none;border-radius:6px;color:#fff;font-size:11px;cursor:pointer;">Upgrade</button>` : ''}</div>`).join('')}</div></div></div><div class="card" style="border-color:rgba(239,68,68,0.3);"><h3 style="font-weight:600;margin-bottom:16px;color:#f87171;">Danger Zone</h3><div style="display:flex;flex-direction:column;gap:16px;"><div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;"><div><div style="color:#cbd5e1;">Cancel Subscription</div><div style="color:#64748b;font-size:13px;">Your plan will remain active until the end of the billing period</div></div><button onclick="cancelSubscription()" style="background:rgba(251,191,36,0.2);border:1px solid rgba(251,191,36,0.5);color:#fbbf24;padding:10px 20px;border-radius:10px;cursor:pointer;font-weight:500;">Cancel Plan</button></div><div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;"><div><div style="color:#cbd5e1;">Delete Account</div><div style="color:#64748b;font-size:13px;">Permanently delete your account and all data</div></div><button onclick="confirmDeleteAccount()" style="background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.5);color:#f87171;padding:10px 20px;border-radius:10px;cursor:pointer;font-weight:500;">Delete Account</button></div></div></div></div>`; }
        function openStripePortal(type) { document.getElementById('billing-modal').classList.remove('hidden'); const title = document.getElementById('billing-modal-title'); const content = document.getElementById('billing-modal-content'); if (type === 'billing') { title.textContent = 'ðŸ’³ Manage Billing'; content.innerHTML = `<div style="margin-bottom:20px;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;border:1px solid rgba(71,85,105,0.3);"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;"><span style="color:#94a3b8;">Current Plan</span><span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;">Active</span></div><div style="font-size:24px;font-weight:bold;color:#22d3ee;margin-bottom:4px;">Professional Plan</div><div style="color:#64748b;font-size:14px;">$99.00 USD / month</div></div><div style="margin-bottom:20px;"><h4 style="font-weight:600;margin-bottom:12px;color:#cbd5e1;">Billing Details</h4><div style="display:flex;flex-direction:column;gap:12px;"><div style="display:flex;justify-content:space-between;padding:12px;background:rgba(15,23,42,0.3);border-radius:8px;"><span style="color:#94a3b8;">Billing Email</span><span style="color:#cbd5e1;">${user.email}</span></div><div style="display:flex;justify-content:space-between;padding:12px;background:rgba(15,23,42,0.3);border-radius:8px;"><span style="color:#94a3b8;">Billing Cycle</span><span style="color:#cbd5e1;">Monthly</span></div><div style="display:flex;justify-content:space-between;padding:12px;background:rgba(15,23,42,0.3);border-radius:8px;"><span style="color:#94a3b8;">Next Payment</span><span style="color:#cbd5e1;">Jan 15, 2025</span></div><div style="display:flex;justify-content:space-between;padding:12px;background:rgba(15,23,42,0.3);border-radius:8px;"><span style="color:#94a3b8;">Amount</span><span style="color:#22d3ee;font-weight:600;">$99.00 USD</span></div></div></div><div style="display:flex;gap:12px;"><button onclick="closeBillingModal()" class="btn" style="flex:1;justify-content:center;">Done</button><button onclick="openStripePortal('payment')" class="btn-secondary" style="flex:1;">Update Payment â†’</button></div>`; } else if (type === 'payment') { title.textContent = 'ðŸ’³ Update Payment Method'; content.innerHTML = `<div style="margin-bottom:20px;padding:16px;background:rgba(15,23,42,0.5);border-radius:12px;border:1px solid rgba(71,85,105,0.3);"><div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;"><div style="width:48px;height:32px;background:linear-gradient(135deg,#1a1f71,#00579f);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:10px;">VISA</div><div><div style="color:#cbd5e1;font-weight:500;">â€¢â€¢â€¢â€¢ â€¢â€¢â€¢â€¢ â€¢â€¢â€¢â€¢ 4242</div><div style="color:#64748b;font-size:12px;">Expires 12/2026</div></div><span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;margin-left:auto;">Default</span></div></div><div style="margin-bottom:20px;"><h4 style="font-weight:600;margin-bottom:12px;color:#cbd5e1;">Add New Card</h4><div style="display:flex;flex-direction:column;gap:12px;"><div><label class="label">Card Number</label><input type="text" class="input" placeholder="1234 5678 9012 3456" maxlength="19" id="card-number"></div><div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"><div><label class="label">Expiry Date</label><input type="text" class="input" placeholder="MM/YY" maxlength="5" id="card-expiry"></div><div><label class="label">CVC</label><input type="text" class="input" placeholder="123" maxlength="4" id="card-cvc"></div></div><div><label class="label">Name on Card</label><input type="text" class="input" placeholder="John Doe" id="card-name"></div></div></div><div style="display:flex;gap:12px;"><button onclick="closeBillingModal()" class="btn-secondary" style="flex:1;">Cancel</button><button onclick="savePaymentMethod()" class="btn" style="flex:1;justify-content:center;">Save Card</button></div><div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(71,85,105,0.3);display:flex;align-items:center;gap:8px;justify-content:center;"><span style="font-size:12px;color:#64748b;">ðŸ”’ Secured by</span><span style="font-size:14px;font-weight:bold;color:#635bff;">stripe</span></div>`; } else if (type === 'invoices') { title.textContent = 'ðŸ“„ Invoice History'; content.innerHTML = `<div style="margin-bottom:20px;"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;"><span style="color:#94a3b8;font-size:14px;">Showing last 6 invoices</span><button onclick="exportAllInvoices()" class="btn-secondary" style="padding:8px 12px;font-size:12px;">â¬‡ Export All</button></div><div style="display:flex;flex-direction:column;gap:8px;">${[{ date: 'Dec 15, 2024', amount: '$99.00', status: 'Paid', id: 'INV-2024-012' }, { date: 'Nov 15, 2024', amount: '$99.00', status: 'Paid', id: 'INV-2024-011' }, { date: 'Oct 15, 2024', amount: '$99.00', status: 'Paid', id: 'INV-2024-010' }, { date: 'Sep 15, 2024', amount: '$99.00', status: 'Paid', id: 'INV-2024-009' }, { date: 'Aug 15, 2024', amount: '$99.00', status: 'Paid', id: 'INV-2024-008' }, { date: 'Jul 15, 2024', amount: '$99.00', status: 'Paid', id: 'INV-2024-007' }].map(inv => `<div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:rgba(15,23,42,0.3);border-radius:8px;border:1px solid rgba(71,85,105,0.3);"><div><div style="color:#cbd5e1;font-weight:500;">${inv.id}</div><div style="color:#64748b;font-size:12px;">${inv.date}</div></div><div style="display:flex;align-items:center;gap:12px;"><span style="color:#22d3ee;font-weight:600;">${inv.amount}</span><span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;font-size:10px;">${inv.status}</span><button onclick="downloadInvoice('${inv.id}')" style="background:none;border:none;color:#64748b;cursor:pointer;padding:4px;">â¬‡</button></div></div>`).join('')}</div></div><div style="display:flex;gap:12px;"><button onclick="closeBillingModal()" class="btn" style="flex:1;justify-content:center;">Close</button></div>`; } }
        function closeBillingModal() { document.getElementById('billing-modal').classList.add('hidden'); }
        function savePaymentMethod() { const num = document.getElementById('card-number').value; const exp = document.getElementById('card-expiry').value; const cvc = document.getElementById('card-cvc').value; const name = document.getElementById('card-name').value; if (!num || !exp || !cvc || !name) { showToast('âš ï¸ Please fill in all card details'); return; } showToast('âœ… Payment method updated!'); closeBillingModal(); }
        function downloadInvoice(id) { showToast(`ðŸ“„ Downloading ${id}...`); setTimeout(() => { const content = `INVOICE: ${id}\nDate: ${new Date().toLocaleDateString()}\nAmount: $99.00 USD\n\nShema K Solutions Corporation\nCopiousK.I.N.G. Enterprise - Professional Plan\n\nThank you for your business!`; const blob = new Blob([content], { type: 'text/plain' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `${id}.txt`; a.click(); URL.revokeObjectURL(url); }, 500); }
        function exportAllInvoices() { showToast('ðŸ“„ Exporting all invoices...'); setTimeout(() => showToast('âœ… Invoices exported!'), 1000); }
        function cancelSubscription() { if (confirm('Are you sure you want to cancel your subscription? You will retain access until the end of your current billing period.')) { showToast('ðŸ“§ Cancellation request submitted. Check your email for confirmation.'); } }
        function saveSettings() { user.name = document.getElementById('settings-name').value; user.email = document.getElementById('settings-email').value; user.company = document.getElementById('settings-company').value; document.getElementById('user-name').textContent = user.name; showToast('âœ… Settings saved!'); }
        function confirmDeleteAccount() { if (confirm('Are you sure you want to delete your account? This cannot be undone.')) { const confirmed = prompt('Type DELETE to confirm account deletion:'); if (confirmed === 'DELETE') { showToast('Account deletion requested. You will receive a confirmation email.'); } else if (confirmed !== null) { showToast('âš ï¸ Deletion cancelled - text did not match.'); } } }

        // Auth functions
        function showAuth(mode) { APP.authMode = mode; document.getElementById('auth-modal').classList.remove('hidden'); switchAuthTab(mode); }
        function closeAuth() { document.getElementById('auth-modal').classList.add('hidden'); }
        function switchAuthTab(tab) { APP.authMode = tab; const lt = document.getElementById('login-tab'), rt = document.getElementById('register-tab'), sb = document.getElementById('auth-submit'), rf = document.getElementById('register-fields'); if (tab === 'login') { lt.style.background = 'linear-gradient(135deg,#0ea5e9,#06b6d4)'; lt.style.color = '#fff'; rt.style.background = 'transparent'; rt.style.color = '#94a3b8'; sb.textContent = 'Sign In'; rf.classList.add('hidden'); } else { rt.style.background = 'linear-gradient(135deg,#0ea5e9,#06b6d4)'; rt.style.color = '#fff'; lt.style.background = 'transparent'; lt.style.color = '#94a3b8'; sb.textContent = 'Create Account'; rf.classList.remove('hidden'); } }
        function handleAuth(e) { e.preventDefault(); const email = document.getElementById('auth-email').value, pw = document.getElementById('auth-password').value; if (!email || !pw) { alert('Please enter email and password'); return; } const sb = document.getElementById('auth-submit'), ot = sb.textContent; sb.textContent = APP.authMode === 'login' ? 'Signing in...' : 'Creating account...'; sb.disabled = true; setTimeout(() => { if (APP.authMode === 'register') { const sm = document.getElementById('success-message'); sm.classList.remove('hidden'); setTimeout(() => sm.classList.add('hidden'), 3000); } closeAuth(); document.getElementById('landing-page').style.display = 'none'; const dp = document.getElementById('dashboard-page'); dp.classList.remove('hidden'); dp.style.display = 'flex'; setTimeout(() => { renderSidebar(); renderMobileSidebar(); setActiveTab('query'); }, 100); document.getElementById('auth-form').reset(); sb.textContent = ot; sb.disabled = false; }, 1200); }

        // Demo triggers on landing page
        function runDemoTrigger(action) { const ra = document.getElementById('demo-response'); ra.style.display = 'block'; ra.innerHTML = `<div style="padding:20px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="display:flex;align-items:center;gap:8px;color:#22d3ee;"><span style="display:inline-block;animation:spin 1s linear infinite;">âŸ³</span> Processing...</div></div>`; setTimeout(() => { ra.innerHTML = `<div style="padding:20px;background:rgba(15,23,42,0.5);border-radius:12px;"><div style="display:flex;align-items:center;gap:8px;color:#10b981;margin-bottom:12px;">âœ“ <span style="font-weight:600;">Here's what I found</span></div><pre style="white-space:pre-wrap;font-family:monospace;font-size:13px;color:#e2e8f0;background:rgba(15,23,42,0.8);padding:16px;border-radius:10px;border:1px solid rgba(71,85,105,0.3);">SAFETY REQUIREMENTS â€” ${action.toUpperCase()}\n${'â”'.repeat(50)}\n\nâœ“ Pre-Entry Requirements\n  â€¢ Complete hazard assessment\n  â€¢ Verify all permits in place\n  â€¢ Ensure proper training\n\nâœ“ Personal Protective Equipment\n  â€¢ Hard hat (Class E or G)\n  â€¢ Safety glasses\n  â€¢ Steel-toe boots\n\nCOMPLIANCE: OSHA 29 CFR 1910/1926 | CSA Z1000\n\nGenerated by K.I.N.G. Framework v17.0\nÂ© 2025 Shema K Solutions Corporation</pre></div>`; }, 1500); }

        // Init
        function init() { switchAuthTab('login'); const dt = document.getElementById('demo-triggers'); if (dt) { const demoTriggers = [{ emoji: 'ðŸ”¥', action: 'Fire safety and emergency procedures' }, { emoji: 'ðŸ”’', action: 'Lockout/Tagout procedures' }, { emoji: 'ðŸ¦º', action: 'PPE requirements' }]; dt.innerHTML = demoTriggers.map(t => `<button onclick="runDemoTrigger('${t.action.replace(/'/g, "\\'")}')" class="industry-tag" style="padding:10px 16px;border-radius:10px;border:1px solid rgba(71,85,105,0.4);background:rgba(15,23,42,0.5);color:#cbd5e1;font-size:13px;display:inline-flex;align-items:center;gap:8px;"><span style="font-size:20px;">${t.emoji}</span>${t.action}</button>`).join(''); } }


const globalHandlers = { openMobileMenu, closeMobileMenu, renderMobileSidebar, handleLogout, renderSidebar, setActiveTab, renderMainContent, getROICard, renderQueryTab, selectIndustry, submitQuery, handleQuery, exportToPDF, exportToJSA, showToast, renderTriggersTab, deleteTrigger, deleteCategory, resetToDefaults, addCategory, addCustomTrigger, exportTriggers, handleTrigger, renderResourcesTab, renderLeaderboardTab, renderKeysTab, toggleAddKey, updateModels, saveNewKey, setDefaultKey, deleteKey, renderTeamTab, removeMember, openInviteModal, closeInviteModal, openUpgradeModal, closeUpgradeModal, processUpgrade, handleInvite, renderUsageTab, renderAuditTab, exportAuditLog, renderSettingsTab, openStripePortal, closeBillingModal, savePaymentMethod, downloadInvoice, exportAllInvoices, cancelSubscription, saveSettings, confirmDeleteAccount, showAuth, closeAuth, switchAuthTab, handleAuth, runDemoTrigger, init };
function bindGlobals() { Object.assign(window, globalHandlers); }
function unbindGlobals() {
  Object.keys(globalHandlers).forEach((key) => {
    if (window[key] === globalHandlers[key]) {
      delete window[key];
    }
  });
}
onMounted(() => {
  bindGlobals();
  loadQueryHistory().finally(() => {
    const container = document.getElementById("audit-only");
    if (container) {
      renderAuditTab(container);
    }
  });
});

onBeforeUnmount(() => {
  unbindGlobals();
});
</script>

<style>        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-10px)
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(14, 165, 233, 0.3)
            }

            50% {
                box-shadow: 0 0 40px rgba(14, 165, 233, 0.6)
            }
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%
            }

            50% {
                background-position: 100% 50%
            }

            100% {
                background-position: 0% 50%
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.9)
            }

            to {
                opacity: 1;
                transform: scale(1)
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg)
            }

            to {
                transform: rotate(360deg)
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(30px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite
        }

        .animate-fade-in {
            animation: fade-in-up 0.6s ease-out forwards
        }

        .animate-scale-in {
            animation: scale-in 0.5s ease-out forwards
        }

        .animate-slide-up {
            animation: slideUp 0.3s ease-out
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #020617 50%, #0f172a 100%);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
        }

        .hidden {
            display: none !important
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #020617 30%, #0c1929 60%, #0f172a 100%);
            position: relative;
            overflow: hidden
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(71, 85, 105, 0.4);
            border-radius: 16px;
            transition: all 0.4s
        }

        .glass-card:hover {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(34, 211, 238, 0.4);
            transform: translateY(-4px)
        }

        .cta-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            border: none;
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s
        }

        .cta-primary:hover {
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.5);
            transform: translateY(-2px)
        }

        .btn {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            border: none;
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.4)
        }

        .btn-secondary {
            background: rgba(51, 65, 85, 0.5);
            border: 1px solid rgba(71, 85, 105, 0.5);
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s
        }

        .btn-secondary:hover {
            background: rgba(71, 85, 105, 0.6)
        }

        .input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid rgba(71, 85, 105, 0.5);
            background: rgba(15, 23, 42, 0.8);
            color: #fff;
            outline: none;
            font-size: 14px;
            transition: all 0.2s
        }

        .input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15)
        }

        .input::placeholder {
            color: #64748b
        }

        .label {
            display: block;
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 8px;
            font-weight: 500
        }

        .badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600
        }

        .card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(71, 85, 105, 0.5);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(12px);
            transition: all 0.3s
        }

        .card:hover {
            border-color: rgba(100, 116, 139, 0.6);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2)
        }

        .stat-counter {
            font-variant-numeric: tabular-nums;
            background: linear-gradient(135deg, #22d3ee, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent
        }

        .industry-tag {
            transition: all 0.3s ease;
            cursor: pointer
        }

        .industry-tag:hover {
            transform: scale(1.05);
            border-color: rgba(34, 211, 238, 0.5);
            background: rgba(34, 211, 238, 0.1)
        }

        .pricing-card-popular {
            position: relative;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(6, 182, 212, 0.1) 100%);
            border: 2px solid rgba(34, 211, 238, 0.5);
            transform: scale(1.02)
        }

        .pricing-card-popular::before {
            content: 'â­ MOST POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700
        }

        .nav-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            border: none;
            margin-bottom: 4px;
            cursor: pointer;
            text-align: left;
            background: transparent;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s
        }

        .nav-btn:hover {
            background: rgba(51, 65, 85, 0.4);
            color: #cbd5e1
        }

        .nav-btn.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2) 0%, rgba(6, 182, 212, 0.1) 100%);
            color: #22d3ee
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid rgba(71, 85, 105, 0.3);
            background: rgba(15, 23, 42, 0.5);
            color: #cbd5e1;
            font-size: 13px;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s
        }

        .quick-action-btn:hover {
            background: rgba(30, 41, 59, 0.8);
            border-color: rgba(100, 116, 139, 0.5);
            transform: translateX(4px)
        }

        .industry-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-align: left;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s
        }

        .industry-btn:hover {
            transform: translateX(4px)
        }

        .roi-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            position: relative;
            overflow: hidden
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(71, 85, 105, 0.3);
            transition: all 0.2s
        }

        .leaderboard-item:hover {
            border-color: rgba(34, 211, 238, 0.5);
            transform: translateX(4px)
        }

        .leaderboard-item.gold {
            border-left: 3px solid #fbbf24
        }

        .leaderboard-item.silver {
            border-left: 3px solid #94a3b8
        }

        .leaderboard-item.bronze {
            border-left: 3px solid #cd7f32
        }

        .achievement-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600
        }

        .achievement-badge.loto {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24
        }

        .achievement-badge.ppe {
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6
        }

        .achievement-badge.fire {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444
        }

        .achievement-badge.electrical {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 100;
            animation: fadeIn 0.2s ease-out
        }

        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 8px
        }

        .mobile-drawer {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            background: rgba(15, 23, 42, 0.98);
            border-right: 1px solid rgba(71, 85, 105, 0.3);
            z-index: 200;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            backdrop-filter: blur(12px);
            display: flex;
            flex-direction: column
        }

        .mobile-drawer.open {
            transform: translateX(0)
        }

        .mobile-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 199;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s
        }

        .mobile-overlay.open {
            opacity: 1;
            pointer-events: auto
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px
        }

        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5)
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.5);
            border-radius: 3px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.6)
        }

        @media(max-width:900px) {

            .landing-grid-2,
            .landing-grid-3 {
                grid-template-columns: 1fr !important
            }

            .landing-grid-4 {
                grid-template-columns: repeat(2, 1fr) !important
            }

            .pricing-grid {
                grid-template-columns: 1fr !important;
                max-width: 400px !important;
                margin: 0 auto !important
            }

            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr) !important
            }

            .query-layout {
                flex-direction: column !important
            }

            .query-sidebar {
                width: 100% !important
            }
        }

        @media(max-width:768px) {
            #dashboard-page aside {
                display: none
            }

            #dashboard-page main {
                width: 100%
            }

            .hamburger-btn {
                display: flex !important
            }
        }

        @media(max-width:480px) {

            .landing-grid-4,
            .dashboard-stats {
                grid-template-columns: 1fr !important
            }
        }
    </style>



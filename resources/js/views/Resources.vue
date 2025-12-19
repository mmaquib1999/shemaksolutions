<template>
  <div class="resources-page" id="main-content">
    <div class="content-wrap">

      <!-- Hero -->
      <section class="hero-card card">
        <div class="hero-head">
          <span class="hero-icon">📚</span>
          <div>
            <h2>Safety Regulatory Resources</h2>
            <p>Quick access to official safety regulations, standards organizations, and training resources. All links open in a new tab.</p>
          </div>
        </div>
      </section>

      <!-- Sections -->
      <section
        v-for="section in sections"
        :key="section.code"
        class="resource-section card"
        :style="{ '--accent': section.accent }"
      >
        <div class="section-head">
          <span class="section-flag">{{ section.flag }}</span>
          <div class="section-title">
            <span class="section-code">{{ section.code }}</span>
            <span class="section-name">{{ section.title }}</span>
          </div>
        </div>

        <div class="resource-grid">
          <a
            v-for="item in section.items"
            :key="item.title"
            class="resource-card"
            :href="item.href"
            target="_blank"
            rel="noopener noreferrer"
          >
            <div class="resource-icon" :style="{ background: item.iconBg || section.iconBg, color: item.iconColor || section.accent }">
              <span>{{ item.icon }}</span>
            </div>
            <div class="resource-copy">
              <div class="resource-title">{{ item.title }}</div>
              <div class="resource-sub">{{ item.subtitle }}</div>
            </div>
            <span class="resource-link">→</span>
          </a>
        </div>
      </section>

    </div>
  </div>
</template>

<script>
const SECTION_DATA = [
  {
    code: "CA",
    flag: "🇨🇦",
    title: "Canadian Safety Regulators",
    accent: "#34d399",
    iconBg: "linear-gradient(135deg, rgba(52,211,153,0.14), rgba(34,197,94,0.08))",
    items: [
      { title: "CCOHS - Canadian Centre for Occupational Health & Safety", subtitle: "National centre for workplace health and safety information", href: "https://www.ccohs.ca/", icon: "🛡️" },
      { title: "WorkSafeBC", subtitle: "British Columbia workplace safety", href: "https://www.worksafebc.com/", icon: "🛡️" },
      { title: "Alberta OHS", subtitle: "Alberta Occupational Health & Safety", href: "https://www.alberta.ca/occupational-health-safety.aspx", icon: "🛡️" },
      { title: "Saskatchewan WorkSafe", subtitle: "Saskatchewan workplace safety", href: "https://www.worksafesask.ca/", icon: "🛡️" },
      { title: "Safe Work Manitoba", subtitle: "Manitoba workplace safety", href: "https://www.safemanitoba.com/", icon: "🛡️" },
      { title: "Ontario MOL", subtitle: "Ontario Ministry of Labour", href: "https://www.ontario.ca/page/workplace-health-and-safety", icon: "🛡️" },
      { title: "CNESST Quebec", subtitle: "Quebec workplace safety commission", href: "https://www.cnesst.gouv.qc.ca/en", icon: "🛡️" },
      { title: "WorkSafeNB", subtitle: "New Brunswick workplace safety", href: "https://www.worksafenb.ca/", icon: "🛡️" },
    ],
  },
  {
    code: "US",
    flag: "🇺🇸",
    title: "US Safety Regulators (OSHA)",
    accent: "#60a5fa",
    iconBg: "linear-gradient(135deg, rgba(96,165,250,0.14), rgba(37,99,235,0.08))",
    items: [
      { title: "OSHA", subtitle: "Occupational Safety and Health Administration", href: "https://www.osha.gov/", icon: "🏛️" },
      { title: "OSHA Standards", subtitle: "Complete OSHA regulations database", href: "https://www.osha.gov/laws-regs/regulations/standardnumber", icon: "📜" },
      { title: "OSHA eTools", subtitle: "Interactive safety guidance tools", href: "https://www.osha.gov/etools", icon: "🛠️" },
      { title: "NIOSH", subtitle: "National Institute for Occupational Safety & Health", href: "https://www.cdc.gov/niosh/", icon: "🩺" },
      { title: "MSHA", subtitle: "Mine Safety and Health Administration", href: "https://www.msha.gov/", icon: "⛏️" },
    ],
  },
  {
    code: "INTL",
    flag: "🌐",
    title: "International Standards",
    accent: "#a78bfa",
    iconBg: "linear-gradient(135deg, rgba(167,139,250,0.14), rgba(126,34,206,0.08))",
    items: [
      { title: "ISO 45001", subtitle: "International OH&S management standard", href: "https://www.iso.org/iso-45001-occupational-health-and-safety.html", icon: "🌐" },
      { title: "ILO OSH", subtitle: "International Labour Organization safety resources", href: "https://www.ilo.org/safework/", icon: "🌐" },
      { title: "HSE UK", subtitle: "UK Health and Safety Executive", href: "https://www.hse.gov.uk/", icon: "🌐" },
      { title: "Safe Work Australia", subtitle: "Australian workplace safety", href: "https://www.safeworkaustralia.gov.au/", icon: "🌐" },
    ],
  },
  {
    code: "STD",
    flag: "📏",
    title: "Standards Organizations",
    accent: "#facc15",
    iconBg: "linear-gradient(135deg, rgba(250,204,21,0.16), rgba(234,179,8,0.08))",
    items: [
      { title: "CSA Group", subtitle: "Canadian Standards Association", href: "https://www.csagroup.org/", icon: "📏" },
      { title: "ANSI", subtitle: "American National Standards Institute", href: "https://www.ansi.org/", icon: "📏" },
      { title: "NFPA", subtitle: "National Fire Protection Association", href: "https://www.nfpa.org/", icon: "📏" },
      { title: "API", subtitle: "American Petroleum Institute standards", href: "https://www.api.org/", icon: "📏" },
      { title: "ASTM", subtitle: "International standards organization", href: "https://www.astm.org/", icon: "📏" },
    ],
  },
  {
    code: "TRN",
    flag: "🎓",
    title: "Training & Certification",
    accent: "#34d399",
    iconBg: "linear-gradient(135deg, rgba(52,211,153,0.14), rgba(16,185,129,0.08))",
    items: [
      { title: "OSHA Training Institute", subtitle: "Official OSHA training programs", href: "https://www.osha.gov/otiec", icon: "🎓" },
      { title: "NSC - National Safety Council", subtitle: "Safety training and resources", href: "https://www.nsc.org/", icon: "🎓" },
      { title: "Red Cross First Aid", subtitle: "First aid certification", href: "https://www.redcross.org/take-a-class/first-aid", icon: "🎓" },
      { title: "St. John Ambulance", subtitle: "Canadian first aid training", href: "https://www.sja.ca/", icon: "🎓" },
    ],
  },
];

export default {
  data() {
    return {
      sections: SECTION_DATA,
    };
  },
};
</script>

<style scoped>
.resources-page {
  flex: 1;
  overflow: auto;
  padding: 24px;
  color: #e2e8f0;
  background: transparent;
}

.content-wrap {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.card {
  background: linear-gradient(145deg, rgba(12, 18, 35, 0.95), rgba(12, 18, 35, 0.85));
  border: 1px solid rgba(71, 85, 105, 0.25);
  border-radius: 16px;
  padding: 18px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
}

.hero-card {
  margin-bottom: 24px;
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(6, 182, 212, 0.05) 100%);
  border: 1px solid rgba(34, 211, 238, 0.3);
  border-radius: 20px;
}

.hero-head {
  display: flex;
  gap: 14px;
  align-items: center;
}

.hero-icon {
  font-size: 32px;
}

.hero-card h2 {
  margin: 0 0 6px;
  font-size: 20px;
  font-weight: 700;
}

.hero-card p {
  margin: 0;
  color: #94a3b8;
}

.resource-section {
  border-radius: 18px;
  border-color: rgba(71, 85, 105, 0.35);
}

.section-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}

.section-flag {
  font-size: 24px;
}

.section-title {
  display: flex;
  align-items: baseline;
  gap: 8px;
  font-weight: 700;
}

.section-code {
  font-size: 16px;
}

.section-name {
  font-size: 16px;
  color: #e2e8f0;
}

.resource-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 12px;
}

.resource-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  border-radius: 12px;
  border: 1px solid rgba(71, 85, 105, 0.4);
  background: linear-gradient(135deg, rgba(19, 27, 45, 0.9), rgba(19, 27, 45, 0.78));
  text-decoration: none;
  color: inherit;
  transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.resource-card:hover {
  border-color: var(--accent);
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
}

.resource-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.resource-copy {
  flex: 1;
  min-width: 0;
}

.resource-title {
  font-weight: 700;
  font-size: 13px;
  color: #e2e8f0;
  margin-bottom: 2px;
}

.resource-sub {
  font-size: 12px;
  color: #94a3b8;
}

.resource-link {
  color: #94a3b8;
  font-size: 14px;
}

@media (max-width: 640px) {
  .resource-card {
    align-items: flex-start;
  }
}
</style>

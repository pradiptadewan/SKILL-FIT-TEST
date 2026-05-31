const items = [
  ['dashboard', 'Ringkasan'],
  ['residents', 'Penghuni'],
  ['houses', 'Rumah'],
  ['payments', 'Pembayaran'],
  ['expenses', 'Pengeluaran'],
  ['reports', 'Laporan'],
]

export function Layout({ activePage, onNavigate, children }) {
  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <span className="brand-mark">RT</span>
          <div><h1>Rukun Warga</h1><small>Administrasi RT</small></div>
        </div>
        <nav className="nav">
          {items.map(([key, label]) => (
            <button key={key} className={activePage === key ? 'active' : ''} onClick={() => onNavigate(key)}>
              {label}
            </button>
          ))}
        </nav>
      </aside>
      <main className="content">{children}</main>
    </div>
  )
}

import { useEffect, useState } from 'react'
import { api } from '../api/client'
import { PageHeader } from '../components/PageHeader'
import { rupiah } from '../utils/format'

export function DashboardPage() {
  const [dashboard, setDashboard] = useState(null)
  const [error, setError] = useState('')
  const year = new Date().getFullYear()

  useEffect(() => {
    api.get(`/dashboard?year=${year}`).then(({ data }) => setDashboard(data)).catch((e) => setError(e.message))
  }, [year])

  const chart = dashboard?.chart ?? []
  const highest = Math.max(1, ...chart.flatMap((item) => [item.income, item.expenses]))

  return (
    <>
      <PageHeader title="Ringkasan Keuangan" subtitle={`Posisi administrasi RT tahun ${year}`} />
      {error && <div className="alert">{error}</div>}
      <div className="grid cards">
        <div className="card"><span className="muted">Pemasukan bulan ini</span><strong>{rupiah(dashboard?.summary.monthly_income)}</strong></div>
        <div className="card"><span className="muted">Pengeluaran bulan ini</span><strong>{rupiah(dashboard?.summary.monthly_expenses)}</strong></div>
        <div className="card"><span className="muted">Saldo akhir</span><strong>{rupiah(dashboard?.summary.ending_balance)}</strong></div>
      </div>
      <section className="panel">
        <h3>Arus kas 12 bulan</h3>
        <div className="legend"><span><i /> Pemasukan</span><span><i className="expense" /> Pengeluaran</span></div>
        <div className="chart">
          {chart.map((item) => (
            <div className="chart-col" key={item.month}>
              <div className="bars">
                <div className="bar" title={rupiah(item.income)} style={{ height: `${(item.income / highest) * 100}%` }} />
                <div className="bar expense" title={rupiah(item.expenses)} style={{ height: `${(item.expenses / highest) * 100}%` }} />
              </div>
              <span>{item.label}</span>
            </div>
          ))}
        </div>
      </section>
    </>
  )
}

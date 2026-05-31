import { useEffect, useState } from 'react'
import { api } from '../api/client'
import { PageHeader } from '../components/PageHeader'
import { currentMonth, rupiah } from '../utils/format'

export function ReportsPage() {
  const [month, setMonth] = useState(currentMonth())
  const [report, setReport] = useState(null)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')

  const load = () => api.get(`/reports/monthly?month=${month}`).then(({ data }) => setReport(data)).catch((e) => setError(e.message))
  useEffect(() => {
    api.get(`/reports/monthly?month=${month}`)
      .then(({ data }) => setReport(data))
      .catch((e) => setError(e.message))
  }, [month])
  const generate = async () => {
    try { const payload = await api.post('/monthly-dues/generate', { month }); setMessage(`${payload.generated_count} tagihan siap diperiksa.`); setError(''); await load() } catch (e) { setError(e.message) }
  }

  return (
    <>
      <PageHeader title="Laporan Bulanan" subtitle="Rekap pemasukan, tagihan, pengeluaran, dan saldo">
        <input type="month" value={month} onChange={(e) => setMonth(e.target.value)} />
        <button className="btn" onClick={generate}>Siapkan tagihan</button>
      </PageHeader>
      <div className="info-box">
        <strong>Fungsi siapkan tagihan:</strong> membuat daftar kewajiban iuran satpam dan kebersihan untuk setiap rumah
        yang tercatat dihuni pada bulan pilihan. Tombol ini aman ditekan ulang karena tagihan yang sama tidak dibuat dua kali.
      </div>
      {error && <div className="alert">{error}</div>}
      {message && <div className="alert success">{message}</div>}
      <div className="grid cards">
        <div className="card"><span className="muted">Pemasukan lunas</span><strong>{rupiah(report?.summary.income)}</strong></div>
        <div className="card"><span className="muted">Pengeluaran</span><strong>{rupiah(report?.summary.expenses)}</strong></div>
        <div className="card"><span className="muted">Saldo akhir</span><strong>{rupiah(report?.summary.ending_balance)}</strong></div>
      </div>
      <section className="panel table-wrap">
        <h3>Detail pemasukan</h3>
        <table><thead><tr><th>Tanggal</th><th>Rumah</th><th>Penghuni</th><th>Rincian</th><th>Total</th></tr></thead><tbody>
          {(report?.payments ?? []).map((payment) => <tr key={payment.id}><td>{payment.paid_at}</td><td>{payment.house.house_number}</td><td>{payment.resident.full_name}</td><td>{payment.details.map((detail) => `${detail.monthly_due.fee_type.name} ${detail.monthly_due.billing_month}`).join(', ')}</td><td>{rupiah(payment.total_amount)}</td></tr>)}
        </tbody></table>
        {!report?.payments.length && <div className="empty">Belum ada pembayaran bulan ini.</div>}
      </section>
      <div className="two-columns">
        <section className="panel table-wrap">
          <h3>Detail tagihan</h3>
          <table><thead><tr><th>Rumah</th><th>Penghuni</th><th>Iuran</th><th>Nominal</th><th>Status</th></tr></thead><tbody>
            {(report?.dues ?? []).map((due) => <tr key={due.id}><td>{due.house.house_number}</td><td>{due.resident.full_name}</td><td>{due.fee_type.name}</td><td>{rupiah(due.amount)}</td><td><span className={`badge ${due.status === 'unpaid' ? 'danger' : ''}`}>{due.status === 'paid' ? 'Lunas' : 'Belum lunas'}</span></td></tr>)}
          </tbody></table>
          {!report?.dues.length && <div className="empty">Generate tagihan untuk melihat detail.</div>}
        </section>
        <section className="panel table-wrap">
          <h3>Detail pengeluaran</h3>
          <table><thead><tr><th>Tanggal</th><th>Nama</th><th>Nominal</th></tr></thead><tbody>
            {(report?.expenses ?? []).map((expense) => <tr key={expense.id}><td>{expense.expense_date}</td><td>{expense.name}</td><td>{rupiah(expense.amount)}</td></tr>)}
          </tbody></table>
          {!report?.expenses.length && <div className="empty">Belum ada pengeluaran bulan ini.</div>}
        </section>
      </div>
    </>
  )
}

import { useEffect, useState } from 'react'
import { api } from '../api/client'
import { PageHeader } from '../components/PageHeader'
import { currentMonth, rupiah, today } from '../utils/format'

const initialForm = { resident_id: '', house_id: '', start_month: currentMonth(), month_count: 1, fee_type_ids: [], paid_at: today(), notes: '' }

export function PaymentsPage() {
  const [payments, setPayments] = useState([])
  const [residents, setResidents] = useState([])
  const [houses, setHouses] = useState([])
  const [feeTypes, setFeeTypes] = useState([])
  const [form, setForm] = useState(initialForm)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')

  const load = async () => {
    try {
      const [paymentPayload, residentPayload, housePayload, feePayload] = await Promise.all([
        api.get('/payments'), api.get('/residents'), api.get('/houses'), api.get('/fee-types'),
      ])
      setPayments(paymentPayload.data); setResidents(residentPayload.data); setHouses(housePayload.data); setFeeTypes(feePayload.data)
    } catch (e) { setError(e.message) }
  }
  useEffect(() => {
    Promise.all([api.get('/payments'), api.get('/residents'), api.get('/houses'), api.get('/fee-types')])
      .then(([paymentPayload, residentPayload, housePayload, feePayload]) => {
        setPayments(paymentPayload.data)
        setResidents(residentPayload.data)
        setHouses(housePayload.data)
        setFeeTypes(feePayload.data)
      })
      .catch((e) => setError(e.message))
  }, [])

  const toggleFee = (id) => setForm((current) => ({
    ...current,
    fee_type_ids: current.fee_type_ids.includes(id) ? current.fee_type_ids.filter((feeId) => feeId !== id) : [...current.fee_type_ids, id],
  }))
  const submit = async (event) => {
    event.preventDefault()
    try {
      await api.post('/payments', form)
      setForm(initialForm); setError(''); setMessage('Pembayaran berhasil dicatat.'); await load()
    } catch (e) { setMessage(''); setError(e.message) }
  }

  return (
    <>
      <PageHeader title="Pembayaran Iuran" subtitle="Catat satpam dan kebersihan untuk satu atau beberapa bulan" />
      {error && <div className="alert">{error}</div>}
      {message && <div className="alert success">{message}</div>}
      <section className="panel">
        <form className="form-grid" onSubmit={submit}>
          <div className="field"><label>Penghuni</label><select required value={form.resident_id} onChange={(e) => setForm({ ...form, resident_id: Number(e.target.value) })}><option value="">Pilih penghuni</option>{residents.map((r) => <option key={r.id} value={r.id}>{r.full_name}</option>)}</select></div>
          <div className="field"><label>Rumah</label><select required value={form.house_id} onChange={(e) => setForm({ ...form, house_id: Number(e.target.value) })}><option value="">Pilih rumah</option>{houses.map((h) => <option key={h.id} value={h.id}>{h.house_number}</option>)}</select></div>
          <div className="field"><label>Mulai bulan</label><input required type="month" value={form.start_month} onChange={(e) => setForm({ ...form, start_month: e.target.value })} /></div>
          <div className="field"><label>Jumlah bulan</label><input required min="1" max="12" type="number" value={form.month_count} onChange={(e) => setForm({ ...form, month_count: Number(e.target.value) })} /></div>
          <div className="field full"><label>Jenis iuran</label><div className="checkboxes">{feeTypes.map((fee) => <label key={fee.id}><input type="checkbox" checked={form.fee_type_ids.includes(fee.id)} onChange={() => toggleFee(fee.id)} /> {fee.name} ({rupiah(fee.amount)})</label>)}</div></div>
          <div className="field"><label>Tanggal bayar</label><input required type="date" value={form.paid_at} onChange={(e) => setForm({ ...form, paid_at: e.target.value })} /></div>
          <div className="field"><label>Catatan</label><input value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} /></div>
          <div className="full"><button className="btn">Catat pembayaran</button></div>
        </form>
      </section>
      <section className="panel table-wrap">
        <h3>Histori pembayaran</h3>
        <table>
          <thead><tr><th>Tanggal</th><th>Penghuni</th><th>Rumah</th><th>Rincian</th><th>Total</th></tr></thead>
          <tbody>{payments.map((payment) => <tr key={payment.id}><td>{payment.paid_at}</td><td>{payment.resident.full_name}</td><td>{payment.house.house_number}</td><td>{payment.details.map((detail) => `${detail.monthly_due.fee_type.name} ${detail.monthly_due.billing_month}`).join(', ')}</td><td>{rupiah(payment.total_amount)}</td></tr>)}</tbody>
        </table>
        {!payments.length && <div className="empty">Belum ada pembayaran.</div>}
      </section>
    </>
  )
}

import { useEffect, useState } from 'react'
import { api } from '../api/client'
import { PageHeader } from '../components/PageHeader'
import { rupiah, today } from '../utils/format'

const initialForm = { name: '', amount: '', expense_date: today(), description: '' }

export function ExpensesPage() {
  const [expenses, setExpenses] = useState([])
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(initialForm)
  const [error, setError] = useState('')

  const load = () => api.get('/expenses').then(({ data }) => setExpenses(data)).catch((e) => setError(e.message))
  useEffect(() => {
    let isActive = true

    api.get('/expenses')
      .then(({ data }) => {
        if (isActive) setExpenses(data)
      })
      .catch((e) => {
        if (isActive) setError(e.message)
      })

    return () => {
      isActive = false
    }
  }, [])
  const submit = async (event) => {
    event.preventDefault()
    try {
      const body = { ...form, amount: Number(form.amount) }
      if (editing) await api.put(`/expenses/${editing}`, body)
      else await api.post('/expenses', body)
      setEditing(null); setForm(initialForm); setError(''); await load()
    } catch (e) { setError(e.message) }
  }
  const edit = (expense) => { setEditing(expense.id); setForm(expense) }
  const remove = async (id) => {
    if (!confirm('Hapus pengeluaran ini?')) return
    try { await api.delete(`/expenses/${id}`); await load() } catch (e) { setError(e.message) }
  }

  return (
    <>
      <PageHeader title="Pengeluaran" subtitle="Catat seluruh biaya operasional lingkungan" />
      {error && <div className="alert">{error}</div>}
      <section className="panel">
        <form className="form-grid" onSubmit={submit}>
          <div className="field"><label>Nama pengeluaran</label><input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} /></div>
          <div className="field"><label>Nominal</label><input required min="1" type="number" value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} /></div>
          <div className="field"><label>Tanggal</label><input required type="date" value={form.expense_date} onChange={(e) => setForm({ ...form, expense_date: e.target.value })} /></div>
          <div className="field"><label>Deskripsi</label><input value={form.description ?? ''} onChange={(e) => setForm({ ...form, description: e.target.value })} /></div>
          <div className="full toolbar"><button className="btn">{editing ? 'Perbarui' : 'Tambah'} pengeluaran</button>{editing && <button type="button" className="btn secondary" onClick={() => { setEditing(null); setForm(initialForm) }}>Batal</button>}</div>
        </form>
      </section>
      <section className="panel table-wrap">
        <table><thead><tr><th>Tanggal</th><th>Pengeluaran</th><th>Deskripsi</th><th>Nominal</th><th>Aksi</th></tr></thead><tbody>
          {expenses.map((expense) => <tr key={expense.id}><td>{expense.expense_date}</td><td>{expense.name}</td><td>{expense.description || '-'}</td><td>{rupiah(expense.amount)}</td><td><div className="toolbar"><button className="btn secondary small" onClick={() => edit(expense)}>Ubah</button><button className="btn danger small" onClick={() => remove(expense.id)}>Hapus</button></div></td></tr>)}
        </tbody></table>
        {!expenses.length && <div className="empty">Belum ada pengeluaran.</div>}
      </section>
    </>
  )
}

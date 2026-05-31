import { useEffect, useState } from 'react'
import { api } from '../api/client'
import { Modal } from '../components/Modal'
import { PageHeader } from '../components/PageHeader'

const initialForm = { full_name: '', resident_status: 'permanent', phone_number: '', is_married: '0', ktp_photo: null }

export function ResidentsPage() {
  const [residents, setResidents] = useState([])
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(initialForm)
  const [error, setError] = useState('')

  const load = () => api.get('/residents').then((payload) => setResidents(payload.data)).catch((e) => setError(e.message))
  useEffect(() => {
    let isActive = true

    api.get('/residents')
      .then((payload) => {
        if (isActive) setResidents(payload.data)
      })
      .catch((e) => {
        if (isActive) setError(e.message)
      })

    return () => {
      isActive = false
    }
  }, [])

  const openForm = (resident = null) => {
    setEditing(resident)
    setIsModalOpen(true)
    setError('')
    setForm(resident ? { ...resident, is_married: resident.is_married ? '1' : '0', ktp_photo: null } : initialForm)
  }

  const submit = async (event) => {
    event.preventDefault()
    try {
      const body = new FormData()
      Object.entries(form).forEach(([key, value]) => value !== null && body.append(key, value))
      if (editing) body.append('_method', 'PATCH')
      await api.post(editing ? `/residents/${editing.id}` : '/residents', body)
      setEditing(null)
      setIsModalOpen(false)
      setForm(initialForm)
      await load()
    } catch (e) { setError(e.message) }
  }

  const remove = async (id) => {
    if (!confirm('Hapus penghuni ini?')) return
    try { await api.delete(`/residents/${id}`); await load() } catch (e) { setError(e.message) }
  }

  return (
    <>
      <PageHeader title="Data Penghuni" subtitle="Kelola identitas dan status penghuni perumahan">
        <button className="btn" onClick={() => openForm()}>Tambah penghuni</button>
      </PageHeader>
      {error && <div className="alert">{error}</div>}
      <section className="panel table-wrap">
        <table>
          <thead><tr><th>KTP</th><th>Nama</th><th>Status</th><th>Telepon</th><th>Menikah</th><th>Rumah aktif</th><th>Aksi</th></tr></thead>
          <tbody>
            {residents.map((resident) => (
              <tr key={resident.id}>
                <td><img className="avatar" src={resident.ktp_photo_url} alt={`KTP ${resident.full_name}`} /></td>
                <td>{resident.full_name}</td>
                <td><span className="badge">{resident.resident_status === 'permanent' ? 'Tetap' : 'Kontrak'}</span></td>
                <td>{resident.phone_number}</td>
                <td>{resident.is_married ? 'Sudah' : 'Belum'}</td>
                <td>{resident.current_occupancy?.house?.house_number ?? '-'}</td>
                <td><div className="toolbar"><button className="btn secondary small" onClick={() => openForm(resident)}>Ubah</button><button className="btn danger small" onClick={() => remove(resident.id)}>Hapus</button></div></td>
              </tr>
            ))}
          </tbody>
        </table>
        {!residents.length && <div className="empty">Belum ada data penghuni.</div>}
      </section>
      {isModalOpen && (
        <Modal title={editing ? 'Ubah penghuni' : 'Tambah penghuni'} onClose={() => { setEditing(null); setIsModalOpen(false); setForm(initialForm) }}>
          {error && <div className="alert">{error}</div>}
          <form className="form-grid" onSubmit={submit}>
            <div className="field full"><label>Nama lengkap</label><input required value={form.full_name} onChange={(e) => setForm({ ...form, full_name: e.target.value })} /></div>
            <div className="field"><label>Status penghuni</label><select value={form.resident_status} onChange={(e) => setForm({ ...form, resident_status: e.target.value })}><option value="permanent">Tetap</option><option value="contract">Kontrak</option></select></div>
            <div className="field"><label>Nomor telepon</label><input required value={form.phone_number} onChange={(e) => setForm({ ...form, phone_number: e.target.value })} /></div>
            <div className="field"><label>Status menikah</label><select value={form.is_married} onChange={(e) => setForm({ ...form, is_married: e.target.value })}><option value="0">Belum menikah</option><option value="1">Sudah menikah</option></select></div>
            <div className="field"><label>Foto KTP {editing && '(opsional)'}</label><input required={!editing} type="file" accept="image/png,image/jpeg,image/webp" onChange={(e) => setForm({ ...form, ktp_photo: e.target.files[0] })} /></div>
            <div className="full"><button className="btn">Simpan penghuni</button></div>
          </form>
        </Modal>
      )}
    </>
  )
}

import { useEffect, useState } from 'react'
import { api } from '../api/client'
import { Modal } from '../components/Modal'
import { PageHeader } from '../components/PageHeader'
import { today } from '../utils/format'

export function HousesPage() {
  const [houses, setHouses] = useState([])
  const [residents, setResidents] = useState([])
  const [selected, setSelected] = useState(null)
  const [houseNumber, setHouseNumber] = useState('')
  const [error, setError] = useState('')
  const [assign, setAssign] = useState({ resident_id: '', started_at: today() })

  const load = async () => {
    try {
      const [housePayload, residentPayload] = await Promise.all([api.get('/houses'), api.get('/residents')])
      setHouses(housePayload.data); setResidents(residentPayload.data)
    } catch (e) { setError(e.message) }
  }
  useEffect(() => {
    Promise.all([api.get('/houses'), api.get('/residents')])
      .then(([housePayload, residentPayload]) => {
        setHouses(housePayload.data)
        setResidents(residentPayload.data)
      })
      .catch((e) => setError(e.message))
  }, [])

  const saveHouse = async (event) => {
    event.preventDefault()
    try { await api.post('/houses', { house_number: houseNumber }); setHouseNumber(''); await load() } catch (e) { setError(e.message) }
  }
  const showHistory = async (house) => {
    try { const { data } = await api.get(`/houses/${house.id}`); setSelected(data) } catch (e) { setError(e.message) }
  }
  const assignResident = async (event) => {
    event.preventDefault()
    try { await api.post(`/houses/${selected.id}/occupancies`, assign); setSelected(null); await load() } catch (e) { setError(e.message) }
  }
  const endOccupancy = async () => {
    try { await api.delete(`/houses/${selected.id}/occupancies/current`, { ended_at: today() }); setSelected(null); await load() } catch (e) { setError(e.message) }
  }

  return (
    <>
      <PageHeader title="Data Rumah" subtitle="Pantau rumah dihuni dan histori penanggung jawab" />
      {error && <div className="alert">{error}</div>}
      <section className="panel">
        <form className="toolbar" onSubmit={saveHouse}>
          <input style={{ maxWidth: 260 }} required placeholder="Nomor rumah, contoh A-01" value={houseNumber} onChange={(e) => setHouseNumber(e.target.value)} />
          <button className="btn">Tambah rumah</button>
        </form>
      </section>
      <section className="panel table-wrap">
        <table>
          <thead><tr><th>Nomor rumah</th><th>Status</th><th>Penghuni aktif</th><th>Mulai dihuni</th><th>Aksi</th></tr></thead>
          <tbody>
            {houses.map((house) => (
              <tr key={house.id}>
                <td><strong>{house.house_number}</strong></td>
                <td><span className={`badge ${house.occupancy_status === 'vacant' ? 'warn' : ''}`}>{house.occupancy_status === 'occupied' ? 'Dihuni' : 'Tidak dihuni'}</span></td>
                <td>{house.current_occupancy?.resident?.full_name ?? '-'}</td>
                <td>{house.current_occupancy?.started_at ?? '-'}</td>
                <td><button className="btn secondary small" onClick={() => showHistory(house)}>Kelola dan histori</button></td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
      {selected && (
        <Modal title={`Rumah ${selected.house_number}`} onClose={() => setSelected(null)}>
          {error && <div className="alert">{error}</div>}
          {selected.current_occupancy ? (
            <div className="panel">
              <strong>{selected.current_occupancy.resident.full_name}</strong>
              <p className="muted">Penghuni aktif sejak {selected.current_occupancy.started_at}</p>
              <button className="btn danger small" onClick={endOccupancy}>Akhiri hunian hari ini</button>
            </div>
          ) : (
            <form className="form-grid" onSubmit={assignResident}>
              <div className="field"><label>Penghuni</label><select required value={assign.resident_id} onChange={(e) => setAssign({ ...assign, resident_id: e.target.value })}><option value="">Pilih penghuni</option>{residents.map((resident) => <option key={resident.id} value={resident.id}>{resident.full_name}</option>)}</select></div>
              <div className="field"><label>Mulai dihuni</label><input required type="date" value={assign.started_at} onChange={(e) => setAssign({ ...assign, started_at: e.target.value })} /></div>
              <div className="full"><button className="btn">Tetapkan penghuni</button></div>
            </form>
          )}
          <div className="panel table-wrap">
            <h3>Histori penghuni</h3>
            <table><thead><tr><th>Penghuni</th><th>Mulai</th><th>Selesai</th></tr></thead><tbody>
              {selected.occupancy_histories.map((history) => <tr key={history.id}><td>{history.resident.full_name}</td><td>{history.started_at}</td><td>{history.ended_at ?? 'Aktif'}</td></tr>)}
            </tbody></table>
          </div>
        </Modal>
      )}
    </>
  )
}

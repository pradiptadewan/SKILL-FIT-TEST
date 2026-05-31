export function Modal({ title, onClose, children }) {
  return (
    <div className="modal-backdrop">
      <div className="modal">
        <div className="modal-head">
          <h3>{title}</h3>
          <button className="btn secondary small" onClick={onClose}>Tutup</button>
        </div>
        {children}
      </div>
    </div>
  )
}

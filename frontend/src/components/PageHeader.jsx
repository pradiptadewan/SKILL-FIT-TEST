export function PageHeader({ title, subtitle, children }) {
  return (
    <div className="page-head">
      <div><h2>{title}</h2><div className="muted">{subtitle}</div></div>
      <div className="toolbar">{children}</div>
    </div>
  )
}

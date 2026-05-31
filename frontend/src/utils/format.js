export const rupiah = (value = 0) => new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
}).format(value)

export const today = () => new Date().toISOString().slice(0, 10)
export const currentMonth = () => new Date().toISOString().slice(0, 7)

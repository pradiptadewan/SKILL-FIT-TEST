import { useState } from 'react'
import { Layout } from './components/Layout'
import { DashboardPage } from './pages/DashboardPage'
import { ResidentsPage } from './pages/ResidentsPage'
import { HousesPage } from './pages/HousesPage'
import { PaymentsPage } from './pages/PaymentsPage'
import { ExpensesPage } from './pages/ExpensesPage'
import { ReportsPage } from './pages/ReportsPage'
import './App.css'

const pages = {
  dashboard: DashboardPage,
  residents: ResidentsPage,
  houses: HousesPage,
  payments: PaymentsPage,
  expenses: ExpensesPage,
  reports: ReportsPage,
}

function App() {
  const [activePage, setActivePage] = useState('dashboard')
  const Page = pages[activePage]

  return (
    <Layout activePage={activePage} onNavigate={setActivePage}>
      <Page />
    </Layout>
  )
}

export default App

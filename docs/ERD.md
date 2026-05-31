# ERD Administrasi RT

```mermaid
erDiagram
    RESIDENTS ||--o{ HOUSE_RESIDENT_HISTORIES : occupies
    HOUSES ||--o{ HOUSE_RESIDENT_HISTORIES : tracks
    HOUSE_RESIDENT_HISTORIES ||--o{ MONTHLY_DUES : becomes_liability
    RESIDENTS ||--o{ MONTHLY_DUES : owes
    HOUSES ||--o{ MONTHLY_DUES : billed_for
    FEE_TYPES ||--o{ MONTHLY_DUES : categorizes
    RESIDENTS ||--o{ PAYMENTS : makes
    HOUSES ||--o{ PAYMENTS : receives_bill_for
    PAYMENTS ||--|{ PAYMENT_DETAILS : contains
    MONTHLY_DUES ||--o| PAYMENT_DETAILS : settled_by

    RESIDENTS {
        bigint id PK
        string full_name
        string ktp_photo_path
        enum resident_status "permanent | contract"
        string phone_number
        boolean is_married
        timestamp deleted_at
    }
    HOUSES {
        bigint id PK
        string house_number UK
        enum occupancy_status "occupied | vacant"
    }
    HOUSE_RESIDENT_HISTORIES {
        bigint id PK
        bigint house_id FK
        bigint resident_id FK
        date started_at
        date ended_at "nullable means active"
    }
    FEE_TYPES {
        bigint id PK
        string code UK
        string name
        bigint amount
        boolean is_active
    }
    MONTHLY_DUES {
        bigint id PK
        bigint house_id FK
        bigint resident_id FK
        bigint house_resident_history_id FK
        bigint fee_type_id FK
        date billing_month
        bigint amount
        enum status "unpaid | paid"
        timestamp paid_at
    }
    PAYMENTS {
        bigint id PK
        bigint resident_id FK
        bigint house_id FK
        date paid_at
        bigint total_amount
        text notes
    }
    PAYMENT_DETAILS {
        bigint id PK
        bigint payment_id FK
        bigint monthly_due_id FK_UK
        bigint amount
    }
    EXPENSES {
        bigint id PK
        string name
        bigint amount
        date expense_date
        text description
    }
```

`monthly_dues` adalah snapshot kewajiban. Nominal dan penghuni penanggung jawab tidak berubah saat tarif atau penghuni rumah berubah di masa depan. `payments` adalah penerimaan kas, sedangkan `payment_details` menghubungkan satu transaksi pembayaran dengan satu atau beberapa tagihan bulanan.

# 🏦 Home Banking Web Application

## ✅ Requisiti Funzionali

### 👥 Tipologie di Utenti
1. **Utenti non autenticati**
2. **Admin**
3. **Clienti**

### 🔐 Servizi Disponibili

#### Utenti non autenticati:
- Visualizzare il sito informativo della banca
- Accedere alla pagina di login
- Effettuare operazioni di recupero credenziali

#### Admin:
- Registrare nuovi clienti
- Rimuovere clienti
- Effettuare logout
- Visualizzare i propri dati personali 

#### Clienti:
- Modificare dati personali (es. indirizzo o numero di cellulare)  
  ⚠️ *La mail (verificata in filiale), username e password non sono modificabili online*
- Effettuare bonifici istantanei
- Visualizzare saldo e movimenti del conto
- Visualizzare i propri dati personali
- Effettuare logout

---

## 🚫 Requisiti Non Funzionali

| Categoria                | Scelte Tecnologiche                     |
|--------------------------|------------------------------------------|
| **Database**             | MySQL                                   |
| **Linguaggi di programmazione** | PHP, JavaScript                   |
| **Markup**               | HTML                                    |
| **Stile**                | CSS                                     |
| **Framework**            | Laravel, Eloquent ORM, Bootstrap        |
| **Metodologia di sviluppo** | Agile                              |

---

## 🛠️ Scelte di Progetto

1. **Pattern MVC**
2. **Autenticazione a due fattori (2FA)**:  
   - **Username / Password**
   - **OTP via email**

---

## 🔒 Sicurezza

1. **MFA (Autenticazione Multi-Fattore)**
2. **Crittografia delle credenziali e dei dati sensibili**
3. **Encoding e sanificazione dell’input**
4. **Validazione dei dati sia lato client che lato server**

---

## 📁 Tecnologie Utilizzate

- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **Backend**: PHP con Laravel Framework
- **Database**: MySQL
- **ORM**: Eloquent (Laravel)

---

## 🌀 Metodologia

- **Agile** – sviluppo iterativo e incrementale con feedback continuo

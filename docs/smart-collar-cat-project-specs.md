# Smart Collar Cat 🐱🔬 — Progetto Collare Intelligente per Gatti

**Ideato da:** Fab  
**Data:** Luglio 2026  
**Ispirazione:** Monitoraggio salute felina in tempo reale dopo esperienza con Cuscino e Sogno (FHV-1)  
**Piattaforma:** Seeed XIAO ESP32S3 Sense

---

## Obiettivo

Collare smart per gatti che monitora parametri vitali in tempo reale, rileva precocemente segni di malattia (febbre, letargia, problemi respiratori, starnuti) e invia alert automatici su Telegram.

---

## Hardware — Seeed XIAO ESP32S3 Sense (il tuo modulo)

### Specifiche del modulo base

| Caratteristica | Valore |
|----------------|--------|
| **MCU** | ESP32-S3 (dual-core Xtensa LX7 @ 240MHz) |
| **WiFi** | 2.4GHz 802.11 b/g/n ✅ |
| **BLE** | BLE 5.0 + Bluetooth Mesh ✅ |
| **PSRAM** | 8MB |
| **Flash** | 8MB |
| **Camera** | Connettore OV2640/OV5640 ✅ **(già incluso)** |
| **Dimensioni** | 21 × 17.5mm |
| **Alimentazione** | 3.3V (regolatore integrato), Battery PWR pin per LiPo |
| **Ricarica** | Built-in charging circuit via USB-C ✅ **(nessun TP4056 esterno!)** |
| **GPIO** | 14 pin accessibili |
| **Interfacce** | I²C, SPI, UART, I²S, ADC |

### Pinout di riferimento

```
                    ┌─────────────┐
  Battery+ ──── PWR │  ╭───────╮  │ 5V ──── USB power
                    │  │ ◉ OV  │  │
  GPIO3 (SDA) ── D3 │  │  ══   │  │ D8  ──── GPIO8
  GPIO4 (SCL) ── D4 │  │       │  │ D9  ──── GPIO9
  GPIO5 ──────── D5 │  ╰───────╯  │ D10 ──── GPIO10
  GPIO6 ──────── D6 │  Camera     │ D0  ──── GPIO0 (boot)
  GPIO7 ──────── D7 │  Connector  │ D1  ──── GPIO1 (TX)
                    └─────────────┘ D2  ──── GPIO2 (RX)
                        ║  ║
                       GND GND
```

- **I²C bus:** D3 (SDA) + D4 (SCL) — per tutti i sensori
- **Alimentazione:** PWR (batteria LiPo) + GND — ricarica via USB-C

---

## Sensori da Aggiungere

### 1. 🌡 Sensore Temperatura — MCP9808

**Scelto perché:** modulo I²C già pronto, ±0.25°C di precisione, range -20°C a +100°C. Il MAX30205 (±0.1°C) è più preciso ma solo in package SMD nudo, non in modulo pronto.

| Specifica | Valore |
|-----------|--------|
| **Modello** | MCP9808 (Adafruit o generico) |
| **Precisione** | ±0.25°C (max ±0.5°C) |
| **Interfaccia** | I²C (indirizzo 0x18, configurabile 0x18-0x1F) |
| **Alimentazione** | 2.7–3.6V (3.3V OK) |
| **Consumo** | 200μA attivo, 0.1μA sleep |
| **Costo** | ~€6 |
| **Link** | [Amazon.it — cerca MCP9808 modulo](https://www.amazon.it/s?k=MCP9808+modulo+temperatura) |
| **Wiring** | VIN→3.3V, GND→GND, SDA→D3, SCL→D4 |

### 2. 💓 Sensore Battito Cardiaco + SpO₂ — MAX30102

| Specifica | Valore |
|-----------|--------|
| **Modello** | MAX30102 (modulo breakout) |
| **Tecnologia** | PPG: LED rossi (660nm) + IR (880nm) |
| **Interfaccia** | I²C (indirizzo 0x57) |
| **Alimentazione** | 1.8–3.3V (modulo include regolatore 1.8V) |
| **Range bpm** | 0.1–200 (adattabile per gatti ~100-240 bpm) |
| **Costo** | ~€8 |
| **Link** | [Amazon.it — Hailege MAX30102](https://www.amazon.it/dp/B07XG2W8H1) |
| **Wiring** | VIN→3.3V, GND→GND, SDA→D3, SCL→D4 |

### 3. 📊 Accelerometro 3-assiale — LIS3DH

| Specifica | Valore |
|-----------|--------|
| **Modello** | Modulo Accelerometro LIS3DH |
| **Range** | ±2g/±4g/±8g/±16g selezionabile |
| **Interfaccia** | I²C (indirizzo 0x18 o 0x19) |
| **Risoluzione** | 16-bit |
| **Consumo** | 2μA in low-power mode |
| **Costo** | ~€3,55 |
| **Link** | [Plexishop.it — LIS3DH modulo](https://www.plexishop.it/it/robotica-ed-automazione/sensori/modulo-accelerometro-lis3dh-a-3-assi-ad-alta-risoluzione.html) |
| **Wiring** | VCC→3.3V, GND→GND, SDA→D3, SCL→D4 |

### 4. 📷 Camera — OV2640 (già inclusa)

La camera sul XIAO ESP32S3 Sense può essere usata per:
- Scatto foto periodico dello stato occhi/naso del gatto
- Riconoscimento visivo di secrezioni, arrossamenti, gonfiori
- Inviare una foto su Telegram per ispezione visiva umana

**IMPORTANTE:** La camera usa pin dedicati (non I²C). Il connettore FPC è già saldato sul modulo — basta inserire il ribbon cable della camera OV2640.

### 5. 🎤 Microfono PDM (opzionale — già presente su ESP32S3 Sense)

Il modulo ESP32S3 Sense **ha già un microfono PDM integrato** sulla versione Sense. Può essere usato per:
- Rilevamento starnuti e tosse
- Analisi della frequenza respiratoria (suono del respiro)
- Rilevamento miagolii anomali (dolore, stress)

---

## Schema di Collegamento (Wiring Diagram)

Tutti i sensori condividono lo stesso bus **I²C** (indirizzi diversi = nessun conflitto):

```
┌────────────────────────────────────────────────┐
│           Seeed XIAO ESP32S3 Sense              │
│                                                  │
│  3.3V ──┬─────────┬──────────┬──────────┐      │
│         │         │          │          │        │
│        VIN       VCC        VIN      3.3V       │
│       MCP9808   LIS3DH   MAX30102   Camera      │
│        SDA──────SDA───────SDA───────            │
│        SCL──────SCL───────SCL───────            │
│        GND──────GND───────GND───────GND───────  │
│                                                  │
│  D3 (SDA)──────────────────────────────────────  │
│  D4 (SCL)──────────────────────────────────────  │
│  PWR ──── LiPo Battery (+)                      │
│  GND ──── LiPo Battery (-)                      │
│                                                  │
│  Camera ─── ribbon cable FPC ─── OV2640/OV5640  │
└──────────────────────────────────────────────────┘
```

**Indirizzi I²C:**
- MCP9808 → **0x18** (default, configurabile)
- LIS3DH → **0x18** ⚠️ **CONFLITTO!** (stesso indirizzo del MCP9808)
- MAX30102 → **0x57**

**Soluzione conflitto I²C:** Il LIS3DH e il MCP9808 hanno entrambi indirizzo 0x18 di default. Due opzioni:
- **A:** Usa un **TCA9548A** (multiplexer I²C, ~€3)
- **B:** Cambia l'indirizzo del LIS3DH a 0x19 (pin SDO/SA0 collegato a 3.3V invece che GND)
- **C:** Usa un **DS18B20** al posto del MCP9808 (bus 1-Wire, pin D5 invece di I²C) — meno preciso ma nessun conflitto
- **Raccomandata: Opzione B** — basta saldare/bridgeare il pin SDO del modulo LIS3DH a 3.3V

---

## Architettura del Sistema

```
┌──────────────────────┐     WiFi     ┌────────────────┐
│  XIAO ESP32S3 Sense   │ ──────────→  │    Telegram     │
│  (collare sul gatto)   │──────────→  │  Bot → Igea     │
│                        │  HTTPS API  │                │
│  [MCP9808  ── temp]   │             └────────────────┘
│  [MAX30102 ── bpm]    │
│  [LIS3DH  ── attività] │
│  [OV2640  ── foto]    │
│  [PDM mic ── starnuti] │
│  [LiPo 100mAh ── batt] │
└──────────────────────┘
        ↑                        ↑
  Ricarica USB-C         Collare breakaway
  ogni 24-48h            (sicurezza gatto)
```

**Vantaggio rispetto a BLE:** WiFi diretto = nessun bisogno del telefono come ponte. Il collare si connette direttamente al WiFi di casa e invia i dati a Telegram. Funziona anche se sei fuori casa.

---

## Firmware — Funzionalità Software

### Ciclo Principale

```
Ogni N minuti:
  1. Leggi MCP9808 → temperatura corporea
  2. Leggi MAX30102 → bpm, SpO₂ (se contatto validato)
  3. Leggi LIS3DH → attività, movimenti, tremori
  4. Valuta stato attuale vs baseline
  5. Se anomalia → alert Telegram immediato
  6. Altrimenti → log periodico (es. ogni 30 min)
  
Ogni 60 minuti (opzionale):
  7. Scatta foto camera OV2640
  8. Invia foto su Telegram
```

### Parametri Fisiologici Felini (Baseline)

| Parametro | Range Normale | Allarme |
|-----------|:------------:|:-------:|
| **Temperatura** | 38.1°C – 39.2°C | >39.5°C = febbre; >41°C = emergenza |
| **Frequenza cardiaca** | 100–240 bpm (cucciolo: 140–240) | >250 o <80 |
| **Attività (LIS3DH)** | Movimenti normali per fascia oraria | Letargia (bassa attività × ore) |
| **Tremori** | Nessuno | Rilevati → possibile febbre alta |
| **Pattern sonno/veglia** | Gatto dorme 12-16h/dì | Sonno eccessivo o irrequietezza |

### Rilevamento Letargia (algoritmo)

```
- Calcola media attività nelle ultime 2 ore (LIS3DH)
- Confronta con media delle stesse ore nei 7 giorni precedenti
- Se attività < 40% del normale → alert "Possibile letargia"
- Se attività < 20% del normale → alert "Letargia severa — controllo urgente"
```

### Rilevamento Tremori Febbrili

```
- Filtro passa-alto su asse Z del LIS3DH (frequenze 8-15 Hz)
- Se ampiezza > soglia per >10 secondi consecutivi
  E temperatura > 39.5°C
  → alert "Febbre alta con tremori"
```

### Rilevamento Starnuti (microfono PDM)

```
- Campionamento 8kHz, FFT su finestre 512 campioni
- Starnuto felino: burst 0.1-0.3s, energia concentrata 0.5-4kHz
- Se pattern rilevato più di 3 volte in 1 ora → alert
```

### Dati da Inviare su Telegram

**Messaggio periodico (es. ogni 30 min):**
```
🐱 Cuscino — Report 14:30
🌡 38.6°C
💓 145 bpm (SpO₂ 98%)
📊 Attivo: sì (riposo: 20min, movimento: 10min)
😴 Pattern: normale
✅ Stato: OK
```

**Alert anomalia immediato:**
```
🚨 ALLERTA — Cuscino
🌡 Temperatura: 40.2°C (FEBBRE)
💓 Battito: 190 bpm
📊 Attività: letargica da 3 ore
📍 Ultima posizione: giardino
📸 [FOTO ALLEGATA]
```

---

## Protocollo di Ricarica

1. Il collare si ricarica via **USB-C** (il XIAO ESP32S3 Sense ha ricarica LiPo integrata)
2. Autonomia stimata: **24-48 ore** con batteria 100mAh (a seconda della frequenza WiFi)
3. Ricarica quotidiana consigliata (abbinata al momento della pappa serale)
4. LED sulla board: rosso = in carica, blu = carico, verde = trasmissione dati

**NOTA:** Il XIAO ESP32S3 Sense ha un regolatore di carica **già integrato** per batterie LiPo collegate al pin PWR. **Non serve TP4056 esterno.** Basta collegare:
- PWR → batteria LiPo (+)
- GND → batteria LiPo (-)
- USB-C → caricatore standard

---

## Stima Costi Finali

| Componente | Costo | Già posseduto? |
|------------|:-----:|:--------------:|
| Seeed XIAO ESP32S3 Sense | ~€20 | ✅ Già tuo |
| MCP9808 (temperatura) | ~€6 | ❌ Da comprare |
| MAX30102 (battito/SpO₂) | ~€8 | ❌ Da comprare |
| LIS3DH (accelerometro) | ~€4 | ❌ Da comprare |
| Camera OV2640 | ~€8 | ✅ Già inclusa? |
| Batteria LiPo 100-150mAh | ~€5 | ❌ Da comprare |
| Collare breakaway | ~€5 | ❌ Da comprare |
| Cavi, connettori, breadboard | ~€5 | ❌ |
| Custodia stampata 3D | ~€10 | ❌ |
| **TOTALE DA COMPRARE** | **~€33-38** | |

**Spesa residua:** circa **€30-35** oltre a ciò che già possiedi.

---

## Roadmap di Sviluppo

### Fase 1 — Setup Ambiente ✅
- [ ] Installare Arduino IDE / PlatformIO
- [ ] Aggiungere supporto ESP32-S3 (board manager URL: `https://espressif.github.io/arduino-esp32/package_esp32_index.json`)
- [ ] Testare blink LED e WiFi
- [ ] Testare camera OV2640

### Fase 2 — Sensori Singoli
- [ ] Cablare MCP9808 su breadboard → leggere temperatura su seriale
- [ ] Cablare LIS3DH → leggere accelerazione su seriale
- [ ] Cablare MAX30102 → leggere bpm su seriale
- [ ] Verificare indirizzi I²C univoci (risolvere conflitto 0x18)

### Fase 3 — Integrazione
- [ ] Firmware che legge TUTTI i sensori ogni N minuti
- [ ] Implementare algoritmo letargia e tremori
- [ ] Implementare soglie di allarme
- [ ] Memorizzazione dati locali (Preferences NVS o LittleFS)

### Fase 4 — Connessione Telegram
- [ ] Bot Telegram + API (usando `UniversalTelegramBot` library)
- [ ] Connessione WiFi automatica
- [ ] Invio report periodici
- [ ] Invio alert immediati per anomalie
- [ ] Comando `/status` via Telegram

### Fase 5 — Camera Integration
- [ ] Scatto foto su comando
- [ ] Invio foto su Telegram
- [ ] Riconoscimento base secrezioni oculari (opzionale AI)

### Fase 6 — Miniaturizzazione
- [ ] PCB custom (o protoboard)
- [ ] Custodia 3D (PLA o TPU flessibile)
- [ ] Collare breakaway
- [ ] Collaudo impermeabilità

### Fase 7 — Validazione su Cuscino
- [ ] Calibrazione baseline per Cuscino
- [ ] Test usura 24h
- [ ] Confronto dati con termometro rettale
- [ ] Validazione soglie allarme

---

## Librerie Arduino Necessarie

| Funzione | Libreria | Link |
|----------|----------|------|
| Board support | **esp32** by Espressif | Package manager |
| MCP9808 | **Adafruit MCP9808** | Library Manager |
| MAX30102 | **SparkFun MAX3010x** | Library Manager |
| LIS3DH | **Adafruit LIS3DH** | Library Manager |
| Camera | **esp32-camera** | GitHub (incluso nel package esp32) |
| Telegram | **UniversalTelegramBot** | Library Manager |
| WiFi | **WiFi.h** | Incluso con esp32 |
| JSON | **ArduinoJson** | Library Manager |

---

## Sfide e Soluzioni

| Problema | Soluzione |
|----------|-----------|
| **Pelo spesso al collo** | LIS3DH e MCP9808 funzionano a contatto indiretto; il MAX30102 richiede contatto pelle su zona rasata (1cm²) |
| **Peso collare** | Target <30g totale; il XIAO pesa ~5g, sensori ~2g cad., batteria ~4g, custodia ~5g = ~22g |
| **Batteria** | Autonomia 24-48h con WiFi ogni 15-30 min; ricarica notturna |
| **Artefatti da movimento** | Il MAX30102 si affida a letture valide solo se LIS3DH rileva gatto fermo |
| **Impermeabilità** | Rivestimento conformale (spray) + custodia; sensori in punti aperti |
| **Sicurezza** | Collare **breakaway** obbligatorio — si sgancia se impigliato |
| **Calibrazione felina** | Algoritmi PPG tarati su frequenze cardiache umane; richiedono adattamento per gatti (bpm × 1.5-2) |
| **Conflitto I²C 0x18** | Opzione B: bridge SDO pin del LIS3DH a 3.3V → indirizzo 0x19 |

---

## Contatti Acquisti Componenti

| Componente | Negozio | Prezzo | Link |
|------------|---------|:------:|------|
| MCP9808 modulo | Amazon.it | ~€6 | [Cerca MCP9808](https://www.amazon.it/s?k=MCP9808+modulo+temperatura) |
| MAX30102 modulo | Amazon.it | €7,99 | [Hailege MAX30102](https://www.amazon.it/dp/B07XG2W8H1) |
| LIS3DH modulo | Plexishop.it | €3,55 | [LIS3DH module](https://www.plexishop.it/it/robotica-ed-automazione/sensori/modulo-accelerometro-lis3dh-a-3-assi-ad-alta-risoluzione.html) |
| LiPo 100-150mAh | Amazon.it | ~€5 | [Cerca LiPo 3.7V](https://www.amazon.it/s?k=batteria+LiPo+100mAh+3.7V) |
| OV2640 camera | Amazon.it | ~€8 | [Cerca OV2640 camera](https://www.amazon.it/s?k=OV2640+camera+modulo+XIAO) |
| Collare breakaway | Amazon.it | ~€5 | [Cerca collare sicurezza gatto](https://www.amazon.it/s?k=collare+sgancio+rapido+gatto) |

---

*Documento creato il 17 Luglio 2026 da Igea per Fab. Per domande o modifiche: chiedi su Telegram.*
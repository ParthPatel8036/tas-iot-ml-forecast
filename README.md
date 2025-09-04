\# IoT Weather + ML Prototype ⛅📈



\[!\[Stack](https://img.shields.io/badge/Stack-PHP%20%7C%20Python%20%7C%20CanvasJS-blue)](#)

\[!\[Transport](https://img.shields.io/badge/Transport-HTTP%20GET-green)](#)

\[!\[Server](https://img.shields.io/badge/Server-Apache%20%7C%20XAMPP-orange)](#)

\[!\[License: MIT](https://img.shields.io/badge/License-MIT-success.svg)](LICENSE)



A small IoT system that \*\*records weather sensor data\*\* (e.g., temp/LDR), \*\*visualizes it\*\* (charts/tables),

and provides simple \*\*ML endpoints\*\* for training/prediction.



Frontend charts use \*\*CanvasJS\*\*. Backend endpoints are \*\*PHP\*\*; a tiny \*\*Python\*\* script can simulate a device.



---



\## ✨ Features

\- 📡 HTTP endpoints to record \& view readings

\- 📊 Dashboard with charts (CanvasJS)

\- 🧠 Simple train / predict endpoints

\- 🧪 Python client (`iot\_device.py`) to simulate device posts

\- 🖥️ Easy to host on XAMPP/WAMP/LAMP



---



\## 🗂 Project structure (high-level)

Assignment 2/

├─ canvasjs3.6/ # charting library (frontend)

├─ models/ # (optional) trained models / artifacts

├─ utils/ # helpers (if provided)

├─ WeatherData/ # raw/large datasets (ignored in git)

├─ index.html # dashboard (charts)

├─ index.php # server landing (optional)

├─ recordtemp.php # ingest endpoint (?value=...\&...)

├─ displaytemp.php # show latest value

├─ convertXMLtoJSON.php # turns XML log into JSON

├─ predict.php # ML predict

├─ train.php # ML train

├─ iot\_device.py # Python client to send readings

├─ tempData\_example.xml # sample data format

├─ screenshots/ # images used in README

└─ media/ # demo video (use Git LFS if >100MB)





---



\## ▶️ Run locally (XAMPP on Windows)



1\) Install \*\*XAMPP\*\*, start \*\*Apache\*\*.  

2\) Copy this folder into:

C:\\xampp\\htdocs\\iot-weather-ml-prototype

3\) Open in your browser:

\- Dashboard: `http://localhost/iot-weather-ml-prototype/index.html`

\- Latest: `http://localhost/iot-weather-ml-prototype/displaytemp.php`

\- Ingest (example):  

&nbsp; `http://localhost/iot-weather-ml-prototype/recordtemp.php?temp=23.5\&lux=130`



4\) Python simulator:

```bash

python iot\_device.py



🔌 Endpoints (example)

| Endpoint               | Method | Query params                   | Purpose                    |

| ---------------------- | ------ | ------------------------------ | -------------------------- |

| `recordtemp.php`       | GET    | e.g. `temp`, `lux`, `time`     | Append a reading to log    |

| `displaytemp.php`      | GET    | —                              | Show/return latest reading |

| `convertXMLtoJSON.php` | GET    | —                              | Serve JSON for charts      |

| `train.php`            | GET    | —                              | Train a simple model       |

| `predict.php`          | GET    | features (e.g. `temp`,`lux`,…) | Return predicted value     |



📸 Screenshots



<table>

&nbsp; <tr>

&nbsp;   <td align="center"><img src="screenshots/dashboard.png" width="280" alt="Dashboard"/><div><sub>dashboard.png</sub></div></td>

&nbsp;   <td align="center"><img src="screenshots/table\_view.png" width="280" alt="Table view"/><div><sub>table\_view.png</sub></div></td>

&nbsp;   <td align="center"><img src="screenshots/predict.png" width="280" alt="Predict endpoint"/><div><sub>predict.png</sub></div></td>

&nbsp; </tr>

</table>



🎥 Demo video



<video src="media/demo.mp4" width="720" controls poster="screenshots/dashboard.png"></video>



🔒 Security



Don’t commit secrets; keep large/raw data out of git (WeatherData/ is ignored).



Add basic auth/token if you ever expose endpoints publicly.



📄 License

MIT - see LICENSE


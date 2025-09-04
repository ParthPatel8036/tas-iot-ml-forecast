from sense_emu import SenseHat
import time
import urllib.request
import json

sense = SenseHat()

day = 1
month = 1
site = 1
mode = "temp"

def scroll_message(msg):
    sense.show_message(msg, scroll_speed=0.05)

def setup_mode():
    global day, month, site
    selection = "day"
    scroll_message("Set Day")
    while True:
        for event in sense.stick.get_events():
            if event.action == "pressed":
                if event.direction == "right":
                    if selection == "day":
                        selection = "month"
                        scroll_message("Set Month")
                    elif selection == "month":
                        selection = "site"
                        scroll_message("Set Site")
                    else:
                        selection = "day"
                        scroll_message("Set Day")
                if event.direction == "left":
                    if selection == "day":
                        selection = "site"
                        scroll_message("Set Site")
                    elif selection == "month":
                        selection = "day"
                        scroll_message("Set Day")
                    else:
                        selection = "month"
                        scroll_message("Set Month")
                if event.direction == "up":
                    if selection == "day" and day < 31:
                        day += 1
                        scroll_message(f"Day {day}")
                    elif selection == "month" and month < 12:
                        month += 1
                        scroll_message(f"Month {month}")
                    elif selection == "site" and site < 5:
                        site += 1
                        scroll_message(f"Site {site}")
                if event.direction == "down":
                    if selection == "day" and day > 1:
                        day -= 1
                        scroll_message(f"Day {day}")
                    elif selection == "month" and month > 1:
                        month -= 1
                        scroll_message(f"Month {month}")
                    elif selection == "site" and site > 1:
                        site -= 1
                        scroll_message(f"Site {site}")
                if event.direction == "middle":
                    scroll_message("Saved")
                    return
        time.sleep(0.1)

def normal_mode(predictions):
    global mode
    min_t = predictions["min_temp"]
    max_t = predictions["max_temp"]
    min_h = predictions["min_hum"]
    max_h = predictions["max_hum"]
    scroll_message("Mode Temp" if mode == "temp" else "Mode Hum")
    while True:
        for event in sense.stick.get_events():
            if event.action == "pressed":
                if event.direction in ("left", "right"):
                    mode = "hum" if mode == "temp" else "temp"
                    scroll_message("Mode Temp" if mode == "temp" else "Mode Hum")
                if event.direction == "middle":
                    return
        current_t = sense.get_temperature()
        current_h = sense.get_humidity()
        if mode == "temp":
            if min_t <= current_t <= max_t:
                sense.clear(0, 255, 0)
            else:
                sense.clear(255, 0, 0)
        else:
            if min_h <= current_h <= max_h:
                sense.clear(0, 0, 255)
            else:
                sense.clear(255, 255, 0)
        time.sleep(0.5)

def main():
    while True:
        setup_mode()
        try:
            url = f"http://iotserver.com/predict.php?day={day}&month={month}&site_id={site}"
            with urllib.request.urlopen(url, timeout=5) as resp:
                body = resp.read().decode('utf-8')
                data = json.loads(body)
            print("Day:", day, "Month:", month, "Site:", site, "Location:", data["location_name"])
            print("MinTemp:", data["min_temp"], "MaxTemp:", data["max_temp"])
            print("MinHum:", data["min_hum"], "MaxHum:", data["max_hum"])
        except Exception as e:
            print("Error fetching prediction:", e)
            continue
        normal_mode(data)

if __name__ == "__main__":
    main()

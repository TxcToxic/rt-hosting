import requests

headers = {
    "Authorization": "RTToken-gjbXEyq3fu9sCme8Om7nojKr6G6zOrzxAdi3KhKjFQd5oBCF7p.sO4bF3l276Ig4a1wv5SGY8fnfRimoOGT"
}

data = {
    "op": "restart",
    "sid": "105"
}

response = requests.post("https://rt-hosting.eu/api/", data=data, headers=headers)

print("Status Code:", response.status_code)
print("Feedback:", response.text)
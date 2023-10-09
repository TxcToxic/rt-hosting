import random
import hashlib
import string

chars = string.ascii_letters + string.digits

rttoken = "RTToken-" + "".join(random.choice(chars) for _ in range(50))
rtsecret = "".join(random.choice(chars) for _ in range(32))
rtsecret_hashed = hashlib.sha512(rtsecret.encode()).hexdigest()

print(rttoken + "\r\n\r\nSecret (DB): " + rtsecret_hashed + "\r\n\r\nSecret (user): " + rtsecret)

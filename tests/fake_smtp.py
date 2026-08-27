#!/usr/bin/env python3
import base64,socket,sys
host=sys.argv[1];port=int(sys.argv[2]);out=sys.argv[3]
with socket.socket() as server:
    server.setsockopt(socket.SOL_SOCKET,socket.SO_REUSEADDR,1);server.bind((host,port));server.listen(1)
    conn,_=server.accept()
    with conn:
        f=conn.makefile('rwb',buffering=0)
        def send(s): f.write((s+'\r\n').encode())
        send('220 fake-smtp ready');auth_stage=0;data=False;message=[]
        while True:
            raw=f.readline()
            if not raw: break
            line=raw.decode(errors='replace').rstrip('\r\n')
            if data:
                if line=='.':
                    Path=None
                    with open(out,'w',encoding='utf-8') as h:h.write('\n'.join(message))
                    send('250 queued');data=False;continue
                if line.startswith('..'): line=line[1:]
                message.append(line);continue
            upper=line.upper()
            if auth_stage==1:
                base64.b64decode(line);auth_stage=2;send('334 UGFzc3dvcmQ6');continue
            if auth_stage==2:
                base64.b64decode(line);auth_stage=0;send('235 authenticated');continue
            if upper.startswith('EHLO '): send('250-fake-smtp');send('250 AUTH LOGIN');continue
            if upper=='AUTH LOGIN': auth_stage=1;send('334 VXNlcm5hbWU6');continue
            if upper.startswith('MAIL FROM:'): send('250 sender ok');continue
            if upper.startswith('RCPT TO:'): send('250 recipient ok');continue
            if upper=='DATA': data=True;send('354 end with .');continue
            if upper=='QUIT': send('221 bye');break
            send('250 ok')

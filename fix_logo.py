from PIL import Image

def fix_transparency(img_path, out_path):
    img = Image.open(img_path).convert("RGBA")
    data = img.getdata()
    
    new_data = []
    for item in data:
        r, g, b, a = item
        # Check if the pixel is gray (R, G, B are similar)
        if abs(r - g) < 10 and abs(g - b) < 10:
            # Target the specific checkerboard grays
            if (75 < r < 105) or (115 < r < 145):
                # Set to transparent
                new_data.append((255, 255, 255, 0))
                continue
        
        # Also clean up intermediate grays between the checkerboard pattern
        if abs(r - g) < 10 and abs(g - b) < 10 and 70 < r < 150:
            new_data.append((255, 255, 255, 0))
            continue
            
        new_data.append(item)
        
    img.putdata(new_data)
    img.save(out_path, "PNG")

fix_transparency("public/robo.png", "public/robo_fixed.png")
print("Done!")

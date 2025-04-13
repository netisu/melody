package main

import (
	"fmt"
	. "github.com/fogleman/fauxgl"
	"github.com/nfnt/resize"
	"os"
	"path/filepath"
	"time"
)

// ... (constants and variable declarations)
const (
	scale        = 4
	width        = 512
	height       = 512
	near         = 1
	far          = 10
	storageDir   = "/var/www/cdn/uploads"
	cdnDirectory = "/var/www/cdn/thumbnails"
)

var (
	eye     = V(-2, 4, 8)
	center  = V(0, 0, 0)
	up      = V(0, 1, 0)
	light   = V(.1, 1, .6).Normalize()
	ambient = HexColor("#828282")
	fovy    = float64(15)
)

func main() {
	if len(os.Args) < 2 {
		fmt.Println("Usage: program_name hash")
		return
	}

	hash := os.Args[1]
	objFilePath := filepath.Join(storageDir, hash+".obj")
	pngFilePath := filepath.Join(storageDir, hash+".png")
	thumbnailFilePath := filepath.Join(cdnDirectory, hash+".png")

	itemMesh, err := LoadOBJ(objFilePath)
	if err != nil {
		fmt.Println("Error loading OBJ file:", err)
		return
	}

	itemTexture, err := LoadTexture(pngFilePath)
	if err != nil {
		fmt.Println("Error loading PNG texture:", err)
		return
	}

	mesh := NewEmptyMesh()
	start := time.Now()
	mesh.Add(itemMesh)
	mesh.BiUnitCube()

	context := NewContext(width*scale, height*scale)
	shader := NewPhongShader(LookAt(eye, center, up).Perspective(fovy, float64(width)/float64(height), near, far), light, eye)
	shader.SpecularPower = 0
	shader.AmbientColor = ambient
	context.Shader = shader
	shader.Texture = itemTexture
	context.DrawMesh(itemMesh)

	thumbnailImage := resize.Resize(width, height, context.Image(), resize.Bilinear)
	err = SavePNG(thumbnailFilePath, thumbnailImage)
	if err != nil {
		fmt.Println("Error saving thumbnail:", err)
		return
	}

	fmt.Println("Thumbnail created:", thumbnailFilePath)
	fmt.Println(time.Since(start))

}

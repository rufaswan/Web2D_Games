// https://stackoverflow.com/questions/7951019/how-to-convert-string-to-float
// https://stackoverflow.com/questions/7245817/converting-float-to-32-bit-hexadecimal-c
// https://stackoverflow.com/questions/21323099/convert-a-hexadecimal-to-a-float-and-viceversa-in-c/21387804
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

// strtof REQUIRES c99
union float_bits {
	unsigned int i;
	float f;
};

void float2hex( char str[] )
{
	union float_bits  bits;
	// atof return double/float64
	bits.f = strtof(str, NULL);
	printf("f2h : %f -> %x\n", bits.f, bits.i);
	return;
}

void hex2float( char str[] )
{
	union float_bits  bits;
	bits.i = strtoul(str, NULL, 16);
	printf("h2f : %x -> %f\n", bits.i, bits.f);
	return;
}

int main( int argc, char* argv[] )
{
	if ( sizeof(int) != sizeof(float) )
		return printf("sizeof(int) != sizeof(float)\n");

	int i, len;
	char* pos;
	for ( i=1; i < argc; i++ )
	{
		pos = strchr(argv[i], '.');
		if ( pos != NULL )
		{
			float2hex(argv[i]);
			continue;
		}

		len = strlen(argv[i]);
		if ( len == 8 )
		{
			hex2float(argv[i]);
			continue;
		}

		printf("UNKNOWN %s\n", argv[i]);
	}
	return 0;
}

program main
  implicit none
  real :: F,C
  C=0.0
  F= 9*C/5+32
  write(*,*) F
 C=100.0
  F= 9*C/5+32
  write(*,*) F
 C=-40.0
  F= 9*C/5+32
  write(*,*) F
  stop
end


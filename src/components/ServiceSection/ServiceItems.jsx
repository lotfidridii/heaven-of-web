import Col from "react-bootstrap/Col";
function ServiceItem(props) {
  return (
      <Col lg="6">
      <div className="service-item">
        <div className="service-icon">
          <i>{props.ServiceIcon}</i>
        </div>
        <div className="service-text">
          <h3>{props.ServiceTitle}</h3>
          <p>{props.ServiceDescription}</p>
        </div>
      </div>
    </Col>    
  );
}

export default ServiceItem;
